# Support Ticket & SLA Management System

A Laravel help desk where customers create tickets, agents resolve them within business-hour SLA deadlines, and late tickets are escalated automatically.

---

## Table of contents

1. [Tech stack](#tech-stack)
2. [Features](#features)
3. [Requirements](#requirements)
4. [Local setup (without Docker)](#local-setup-without-docker)
5. [Database and seed data](#database-and-seed-data)
6. [Queue and scheduler](#queue-and-scheduler)
7. [Docker setup](#docker-setup)
8. [Running tests](#running-tests)
9. [CSV import format](#csv-import-format)
10. [Architecture and decisions](#architecture-and-decisions)
11. [Assumptions](#assumptions)
12. [Project structure](#project-structure)

---

## Tech stack

- PHP 8.3+, Laravel 13, MySQL 8
- Blade, plain CSS and JavaScript (AJAX)
- Database queue and scheduler
- Docker (optional)

---

## Features

- Roles and permissions (Admin, Agent, Customer)
- Ticket lifecycle with unique ticket numbers
- SLA in business hours, with pause while waiting for the customer
- Automatic escalation (approaching and breached)
- Assignment: manual, automatic and claim
- My queue and Unassigned views
- Replies and internal notes
- Attachments
- Email and in-app notifications
- Search, filters and pagination
- Dashboard
- CSV import
- User management
- Activity history

---

## Requirements

- PHP 8.3+
- Composer 2
- MySQL 8

Or Docker with Docker Compose (see [Docker setup](#docker-setup)).

---

## Local setup (without Docker)

```bash
git clone <repository-url> support-ticket-sla
cd support-ticket-sla

composer install
cp .env.example .env
php artisan key:generate
```

Create two MySQL databases: `ticket_sla_management_sys` for the app and `ticket_sla_management_sys_test` for the tests.

Update `.env` (in `.env.example` the `DB_` lines are commented out; remove the `#`):

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticket_sla_management_sys
DB_USERNAME=your_user
DB_PASSWORD=your_password

QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=3700

MAIL_MAILER=log
```

- `MAIL_MAILER=log` writes emails to `storage/logs/laravel.log`, so no mail server is needed.
- `DB_QUEUE_RETRY_AFTER` must be longer than the import job timeout (3600 seconds).

Then:

```bash
php artisan migrate --seed
php artisan serve
```

Open http://localhost:8000 and log in with one of the [demo users](#demo-users).

Start the queue worker and the scheduler in two more terminals (see [Queue and scheduler](#queue-and-scheduler)).

---

## Database and seed data

`migrate --seed` creates:

- roles and permissions;
- 4 categories: Billing, Technical, Account, General;
- SLA policies: Critical 2 h, High 8 h, Medium 24 h, Low 48 h (business hours);
- business hours: Sunday to Thursday, 09:00 to 18:00;
- 4 holidays;
- demo users.

To wipe everything and start again: `php artisan migrate:fresh --seed`

### Demo users

All demo users have the password `password`. These are seeded demo accounts for local review, not real credentials.

| Role | Email | Category skills |
|---|---|---|
| Admin | admin@supportdesk.com | - |
| Agent | support_agent_01@supportdesk.com | Billing, Technical, Account |
| Agent | support_agent_02@supportdesk.com | Technical, Account |
| Agent | support_agent_03@supportdesk.com | Billing, Technical |
| Customer | user_01@test.com | - |
| Customer | user_02@test.com | - |
| Customer | user_03@test.com | - |

---

## Queue and scheduler

Both are required. Run each in its own terminal:

```bash
php artisan queue:work --queue=default,imports --tries=3 --timeout=3600
php artisan schedule:work
```

- The **queue** sends emails and processes large CSV imports.
- The **scheduler** runs `tickets:check-sla` every minute to escalate tickets.

---

## Docker setup

Docker runs the app, Nginx, queue, scheduler and Mailpit. **MySQL stays outside Docker**; use a MySQL user that can connect from Docker (not `root@localhost`).

Start the containers and enter the app container:

```bash
docker compose up -d --build
docker exec -it support-ticket-sla-app bash
```

Inside the container, create `.env`:

```bash
cp .env.example .env
```

Update `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=host.docker.internal
DB_PORT=3306
DB_DATABASE=ticket_sla_management_sys
DB_USERNAME=your_user
DB_PASSWORD=your_password

QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=3700

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

Then:

```bash
composer install
php artisan key:generate
php artisan migrate --seed
chown -R www-data:www-data storage bootstrap/cache
exit
docker compose restart queue scheduler
```

- App: http://localhost:8000
- Emails (Mailpit): http://localhost:8025

---

## Running tests

- Tests use a separate MySQL database, `ticket_sla_management_sys_test` (set in `phpunit.xml`), so app data is never touched. Each test rolls its data back.
- No email is sent during tests (`Notification::fake()`).

```bash
php artisan test                  # all tests
php artisan test --group=ticket   # ticket tests
php artisan test --group=sla      # SLA tests
php artisan test --group=import   # CSV import tests
```

Save an HTML test report (the `reports/` folder is gitignored):

```bash
php artisan test --testdox-html reports/tests.html
```

Open `reports/tests.html` in a browser.

---

## CSV import format

Admins upload a CSV from **Imports → New import** (a template is available there).

```csv
external_reference,subject,description,priority,category,customer_email,agent_email
LEGACY-1001,Cannot log in,Password reset email never arrives,high,Account,user_01@test.com,
```

- Up to 200 rows are imported immediately; larger files run in the queue.
- Invalid rows are listed with the reason; valid rows are still imported.
- The same `external_reference` is never imported twice.

---

## Architecture and decisions

- **Controller → Service → Repository.** Controllers handle the request, services hold the business logic and transactions, repositories hold the queries.
- **Authorization.** Permissions are stored in the database. `TicketPolicy` checks access per ticket. Customers get a 404 for tickets that are not theirs.
- **SLA.** Deadlines count only business hours and skip holidays. *Waiting for Customer* pauses the clock. The deadline is saved on the ticket, so filtering is plain SQL.
- **Escalation.** A scheduled command runs every minute. Level 1 = approaching, level 2 = breached. Each level happens only once per ticket.
- **Assignment.** New tickets go to the available agent with the right category and the fewest open tickets. Claiming is one atomic update, so two agents cannot claim the same ticket.
- **Notifications.** Events fire after the transaction commits; notifications are queued, so email never slows down the request.
- **CSV import.** The file is streamed row by row and saved in chunks of 200, each in a transaction.
- **Attachments.** Stored privately and downloaded only after an authorization check.
- **Database.** Foreign keys, indexes on filter columns, a full-text index for search, soft deletes and pagination.

---

## Assumptions

- One set of business hours and holidays, in UTC.
- Only customers create tickets; admins can also import them.
- The SLA measures time to resolution.
- Reopening a ticket keeps its original deadline.
- Closed tickets cannot be changed.
- Users are created by an admin (no registration).

---

## Project structure

```
app/
├── Console/Commands/        CheckSlaCommand (tickets:check-sla)
├── Enums/V1/                TicketStatus, TicketPriority, SlaStatus, ActivityType, ...
├── Events/V1/               TicketCreated, TicketAssigned, TicketReplied, SlaBreached, ...
├── Http/Controllers/V1/     Ticket, TicketReply, TicketAssignment, Attachment, Import, User, ...
├── Jobs/V1/                 ProcessTicketImportJob
├── Listeners/V1/            SendTicketNotifications
├── Models/                  User
├── Models/V1/               Ticket, TicketReply, TicketActivity, Attachment, SlaPolicy, ...
├── Notifications/V1/        One class per notification type
├── Policies/V1/             TicketPolicy
├── Repositories/V1/         One repository per table
└── Services/V1/             Business logic
    ├── Assignment/          AutoAssignmentStrategy, LeastLoadedAssignmentStrategy
    └── Import/              TicketCsvReader
config/tickets.php           Ticket, attachment and import settings
database/migrations/         Schema
database/seeders/            Roles, permissions, categories, SLA policies, business hours, holidays, users
tests/                       Unit and feature tests
docker/, Dockerfile, docker-compose.yml
```

<?php

namespace App\Enums\V1;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingForCustomer = 'waiting_for_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::WaitingForCustomer => 'Waiting for Customer',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }

    /**
     * Active tickets still count towards SLA and appear in the active list.
     */
    public function isActive(): bool
    {
        return in_array($this, self::active(), true);
    }

    /**
     * The SLA clock is paused while waiting on the customer.
     */
    public function pausesSla(): bool
    {
        return $this === self::WaitingForCustomer;
    }

    /**
     * Statuses this status may move to.
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [self::InProgress, self::WaitingForCustomer, self::Resolved, self::Closed],
            self::InProgress => [self::WaitingForCustomer, self::Resolved, self::Closed],
            self::WaitingForCustomer => [self::InProgress, self::Resolved, self::Closed],
            self::Resolved => [self::InProgress, self::Closed],
            self::Closed => [],
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, $this->allowedTransitions(), true);
    }

    /**
     * @return array<int, self>
     */
    public static function active(): array
    {
        return [self::Open, self::InProgress, self::WaitingForCustomer];
    }

    /**
     * @return array<int, self>
     */
    public static function finished(): array
    {
        return [self::Resolved, self::Closed];
    }
}

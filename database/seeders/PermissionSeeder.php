<?php

namespace Database\Seeders;

use App\Models\V1\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'ticket.view_all',
                'display_name' => 'View all tickets',
                'group' => 'tickets'
            ],
            [
                'name' => 'ticket.view_own',
                'display_name' => 'View own tickets',
                'group' => 'tickets'
            ],
            [
                'name' => 'ticket.create',
                'display_name' => 'Create tickets',
                'group' => 'tickets'
            ],
            [
                'name' => 'ticket.update',
                'display_name' => 'Edit tickets',
                'group' => 'tickets'
            ],
            [
                'name' => 'ticket.change_status',
                'display_name' => 'Change ticket status',
                'group' => 'tickets'
            ],
            [
                'name' => 'ticket.assign',
                'display_name' => 'Assign tickets to agents',
                'group' => 'tickets'
            ],
            [
                'name' => 'ticket.claim',
                'display_name' => 'Accept unassigned tickets',
                'group' => 'tickets'
            ],
            [
                'name' => 'ticket.reply',
                'display_name' => 'Reply to tickets',
                'group' => 'conversation'
            ],
            [
                'name' => 'ticket.internal_note',
                'display_name' => 'Add internal notes',
                'group' => 'conversation'
            ],
            [
                'name' => 'user.manage',
                'display_name' => 'Manage users',
                'group' => 'administration'
            ],
            [
                'name' => 'settings.manage',
                'display_name' => 'Manage SLA, business hours, holidays and categories',
                'group' => 'administration'
            ],
            [
                'name' => 'import.run',
                'display_name' => 'Import tickets from CSV',
                'group' => 'administration'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }
    }
}

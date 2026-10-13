<?php

namespace Database\Seeders;

use App\Enums\V1\UserRole;
use App\Models\V1\Permission;
use App\Models\V1\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => UserRole::Admin->value,
                'display_name' => 'Administrator',
                'description' => 'Full administrative access',
                'permissions' => [
                    'ticket.view_all', 'ticket.update', 'ticket.change_status',
                    'ticket.assign', 'ticket.reply', 'ticket.internal_note',
                    'user.manage', 'import.run',
                ],
            ],
            [
                'name' => UserRole::Agent->value,
                'display_name' => 'Support Agent',
                'description' => 'Handles and resolves tickets',
                'permissions' => [
                    'ticket.view_all', 'ticket.update', 'ticket.change_status',
                    'ticket.claim', 'ticket.reply', 'ticket.internal_note',
                ],
            ],
            [
                'name' => UserRole::Customer->value,
                'display_name' => 'Customer',
                'description' => 'Creates and follows own tickets',
                'permissions' => [
                    'ticket.view_own', 'ticket.create', 'ticket.reply',
                ],
            ],
        ];

        $permissionIds = Permission::pluck('id', 'name');

        foreach ($roles as $data) {
            $permissions = $data['permissions'];
            unset($data['permissions']);

            $role = Role::updateOrCreate(['name' => $data['name']], $data);
            $role->permissions()->sync($permissionIds->only($permissions)->values()->all());
        }
    }
}

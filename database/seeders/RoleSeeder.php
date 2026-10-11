<?php

namespace Database\Seeders;

use App\Models\V1\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin', 
                'display_name' => 'Administrator', 
                'description' => 'Full administrative access'
            ],
            [
                'name' => 'agent', 
                'display_name' => 'Support Agent', 
                'description' => 'Handles and resolves tickets'
            ],
            [
                'name' => 'customer', 
                'display_name' => 'Customer', 
                'description' => 'Creates and follows own tickets'
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}

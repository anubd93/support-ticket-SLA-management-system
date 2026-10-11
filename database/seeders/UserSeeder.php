<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\V1\Role;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roleIds = Role::pluck('id', 'name');

        $users = [
            [
                'role' => 'admin', 
                'name' => 'Administrator', 
                'email' => 'admin@supportdesk.com'
            ],
            [
                'role' => 'agent', 
                'name' => 'Support Agent', 
                'email' => 'support_agent@supportdesk.com'
            ],
            [
                'role' => 'customer', 
                'name' => 'User 01', 
                'email' => 'user_01@test.com'
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'role_id' => $roleIds[$user['role']],
                    'name' => $user['name'],
                    'password' => 'password', // hashed by the User model's "hashed" cast
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'is_available' => true,
                ]
            );
        }
    }
}

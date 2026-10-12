<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\V1\Category;
use App\Models\V1\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roleIds = Role::pluck('id', 'name');
        $categoryIds = Category::pluck('id', 'slug');

        $users = [
            [
                'role' => 'admin',
                'name' => 'Administrator',
                'email' => 'admin@supportdesk.com'
            ],
            [
                'role' => 'agent',
                'name' => 'Support Agent 01',
                'email' => 'support_agent_01@supportdesk.com',
                'categories' => ['billing', 'technical', 'account'],
            ],
            [
                'role' => 'agent',
                'name' => 'Support Agent 02',
                'email' => 'support_agent_02@supportdesk.com',
                'categories' => ['technical', 'account'],
            ],
            [
                'role' => 'agent',
                'name' => 'Support Agent 03',
                'email' => 'support_agent_03@supportdesk.com',
                'categories' => ['billing', 'technical'],
            ],
            [
                'role' => 'customer',
                'name' => 'User 01',
                'email' => 'user_01@test.com'
            ],
            [
                'role' => 'customer',
                'name' => 'User 02',
                'email' => 'user_02@test.com'
            ],
            [
                'role' => 'customer',
                'name' => 'User 03',
                'email' => 'user_03@test.com'
            ],
        ];

        foreach ($users as $user) {
            $model = User::updateOrCreate(
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

            foreach ($user['categories'] ?? [] as $slug) {
                // category_user has no model; insertOrIgnore keeps the seeder re-runnable (composite primary key).
                DB::table('category_user')->insertOrIgnore([
                    'category_id' => $categoryIds[$slug],
                    'user_id' => $model->id,
                ]);
            }
        }
    }
}

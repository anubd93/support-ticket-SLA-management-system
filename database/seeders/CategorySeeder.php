<?php

namespace Database\Seeders;

use App\Models\V1\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Billing',
                'slug' => 'billing',
                'description' => 'Invoices, payments and refunds'
            ],
            [
                'name' => 'Technical',
                'slug' => 'technical',
                'description' => 'Bugs, errors and outages'
            ],
            [
                'name' => 'Account',
                'slug' => 'account',
                'description' => 'Login, profile and access'
            ],
            [
                'name' => 'General',
                'slug' => 'general',
                'description' => 'Questions and other requests'
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category + ['is_active' => true]);
        }
    }
}

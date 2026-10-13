<?php

namespace Tests;

use App\Models\User;
use App\Models\V1\Category;
use Database\Seeders\BusinessHourSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SlaPolicySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Notification;

/**
 * Every test starts from the same known state:
 * - RefreshDatabase: migrations on the separate test database (phpunit.xml), and each test
 *   runs inside a transaction that is rolled back afterwards.
 * - The reference data the app needs: permissions, roles, categories, SLA policies,
 *   business hours (Sun-Thu 09:00-18:00). No holidays: tests add them when needed.
 * - Notification::fake(): nothing is really sent; tests can assert who would receive what.
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            PermissionSeeder::class,
            RoleSeeder::class,
            CategorySeeder::class,
            SlaPolicySeeder::class,
            BusinessHourSeeder::class,
        ]);

        Notification::fake();
    }

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    protected function customer(): User
    {
        return User::factory()->customer()->create();
    }

    /**
     * An agent with skills in the given seeded categories (by slug).
     */
    protected function agent(array $categorySlugs = []): User
    {
        $agent = User::factory()->agent()->create();
        $agent->categories()->sync(Category::whereIn('slug', $categorySlugs)->pluck('id')->all());

        return $agent;
    }

    protected function category(string $slug): Category
    {
        return Category::where('slug', $slug)->firstOrFail();
    }
}

<?php

namespace Database\Factories;

use App\Enums\V1\UserRole;
use App\Models\User;
use App\Models\V1\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => fn () => $this->roleId(UserRole::Customer),
            'is_active' => true,
            'is_available' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role_id' => $this->roleId(UserRole::Admin)]);
    }

    public function agent(): static
    {
        return $this->state(fn () => ['role_id' => $this->roleId(UserRole::Agent)]);
    }

    public function customer(): static
    {
        return $this->state(fn () => ['role_id' => $this->roleId(UserRole::Customer)]);
    }
    
    public function unavailable(): static
    {
        return $this->state(fn () => ['is_available' => false]);
    }

    private function roleId(UserRole $role): int
    {
        return (int) Role::where('name', $role->value)->value('id');
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}

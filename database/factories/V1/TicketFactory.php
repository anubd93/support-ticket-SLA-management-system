<?php

namespace Database\Factories\V1;

use App\Enums\V1\TicketPriority;
use App\Enums\V1\TicketSource;
use App\Enums\V1\TicketStatus;
use App\Models\User;
use App\Models\V1\Category;
use App\Models\V1\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    public function definition(): array
    {
        return [
            'ticket_number' => sprintf('TKT-%d-%06d', now()->year, fake()->unique()->numberBetween(900000, 999999)),
            'subject' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'priority' => TicketPriority::Medium,
            'status' => TicketStatus::Open,
            'source' => TicketSource::Web,
            'category_id' => Category::factory(),
            'created_by' => User::factory()->customer(),
            'assigned_to' => null,
            'sla_due_at' => now()->addDay(),
            'sla_warning_at' => now()->addHours(20),
        ];
    }

    public function assignedTo(User $agent): static
    {
        return $this->state(fn () => ['assigned_to' => $agent->id, 'assigned_at' => now()]);
    }

    public function status(TicketStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}

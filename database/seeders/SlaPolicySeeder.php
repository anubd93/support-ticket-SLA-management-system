<?php

namespace Database\Seeders;

use App\Enums\V1\TicketPriority;
use App\Models\V1\SlaPolicy;
use Illuminate\Database\Seeder;

class SlaPolicySeeder extends Seeder
{
    public function run(): void
    {
        // Times are in minutes.
        $policies = [
            [
                'priority' => TicketPriority::Low->value,
                'resolution_minutes' => 48 * 60,
                'first_response_minutes' => 8 * 60,
                'warning_threshold_percent' => 20
            ],
            [
                'priority' => TicketPriority::Medium->value,
                'resolution_minutes' => 24 * 60,
                'first_response_minutes' => 4 * 60,
                'warning_threshold_percent' => 20
            ],
            [
                'priority' => TicketPriority::High->value,
                'resolution_minutes' => 8 * 60,
                'first_response_minutes' => 60,
                'warning_threshold_percent' => 25
            ],
            [
                'priority' => TicketPriority::Critical->value,
                'resolution_minutes' => 2 * 60,
                'first_response_minutes' => 30,
                'warning_threshold_percent' => 25
            ],
        ];

        foreach ($policies as $policy) {
            SlaPolicy::updateOrCreate(['priority' => $policy['priority']], $policy + ['is_active' => true]);
        }
    }
}

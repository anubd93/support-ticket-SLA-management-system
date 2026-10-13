<?php

namespace Database\Seeders;

use App\Models\V1\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        // Sample holidays: SLA time does not count on these dates.
        $holidays = [
            [
                'date' => '2026-12-16', 
                'name' => 'Victory Day'
            ],
            [
                'date' => '2026-12-25', 
                'name' => 'Christmas Day'
            ],
            [
                'date' => '2027-02-21', 
                'name' => 'International Mother Language Day'
            ],
            [
                'date' => '2027-03-26', 
                'name' => 'Independence Day'
            ],
        ];

        foreach ($holidays as $holiday) {
            Holiday::updateOrCreate(['date' => $holiday['date']], $holiday);
        }
    }
}

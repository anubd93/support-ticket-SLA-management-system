<?php

namespace Database\Seeders;

use App\Models\V1\BusinessHour;
use Illuminate\Database\Seeder;

class BusinessHourSeeder extends Seeder
{
    public function run(): void
    {
        // day_of_week follows Carbon: 0 = Sunday ... 6 = Saturday.
        // Working week: Sunday to Thursday, 09:00 to 18:00.
        $workingDays = [0, 1, 2, 3, 4];

        foreach (range(0, 6) as $day) {
            $isWorking = in_array($day, $workingDays, true);

            BusinessHour::updateOrCreate(['day_of_week' => $day], [
                'is_working_day' => $isWorking,
                'start_time' => $isWorking ? '09:00:00' : null,
                'end_time' => $isWorking ? '18:00:00' : null,
            ]);
        }
    }
}

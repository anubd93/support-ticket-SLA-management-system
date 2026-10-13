<?php

namespace App\Repositories\V1;

use Illuminate\Support\Facades\DB;

class TicketSequenceRepositoryV1
{
    private $year, $last_number;

    public function setYear($year)
    {
        $this->year = $year;
        return $this;
    }

    public function setLastNumber($last_number)
    {
        $this->last_number = $last_number;
        return $this;
    }

    public function nextNumber(): int
    {
        DB::table('ticket_sequences')->insertOrIgnore([
            'year' => $this->year,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $current = DB::table('ticket_sequences')
            ->where('year', $this->year)
            ->lockForUpdate()
            ->value('last_number');

        $next = $current + 1;

        DB::table('ticket_sequences')
            ->where('year', $this->year)
            ->update(['last_number' => $next, 'updated_at' => now()]);

        return $next;
    }
}

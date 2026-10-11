<?php

namespace App\Repositories\V1;

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
}

<?php

namespace App\Repositories\V1;

class BusinessHourRepositoryV1
{
    private $id, $day_of_week, $is_working_day, $start_time, $end_time, $created_at;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setDayOfWeek($day_of_week)
    {
        $this->day_of_week = $day_of_week;
        return $this;
    }

    public function setIsWorkingDay($is_working_day)
    {
        $this->is_working_day = $is_working_day;
        return $this;
    }

    public function setStartTime($start_time)
    {
        $this->start_time = $start_time;
        return $this;
    }

    public function setEndTime($end_time)
    {
        $this->end_time = $end_time;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }
}

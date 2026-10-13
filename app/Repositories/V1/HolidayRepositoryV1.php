<?php

namespace App\Repositories\V1;

use Illuminate\Support\Facades\DB;

class HolidayRepositoryV1
{
    private $id, $date, $name, $created_at;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * Holiday dates as a lookup set: ['2026-12-16' => true, ...].
     */
    public function getDates(): array
    {
        return DB::table('holidays')
            ->whereNull('deleted_at')
            ->pluck('date')
            ->mapWithKeys(fn ($date) => [substr((string) $date, 0, 10) => true])
            ->all();
    }
}

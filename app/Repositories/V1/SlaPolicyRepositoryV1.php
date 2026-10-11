<?php

namespace App\Repositories\V1;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SlaPolicyRepositoryV1
{
    private $id, $priority, $resolution_minutes, $first_response_minutes,
        $warning_threshold_percent, $is_active, $created_at;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    public function setResolutionMinutes($resolution_minutes)
    {
        $this->resolution_minutes = $resolution_minutes;
        return $this;
    }

    public function setFirstResponseMinutes($first_response_minutes)
    {
        $this->first_response_minutes = $first_response_minutes;
        return $this;
    }

    public function setWarningThresholdPercent($warning_threshold_percent)
    {
        $this->warning_threshold_percent = $warning_threshold_percent;
        return $this;
    }

    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getResolutionMinutesByPriority(): ?int
    {
        return DB::table('sla_policies')
            ->where('priority', $this->priority)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->value('resolution_minutes');
    }

    /**
     * @return Collection<string, int> priority => resolution_minutes
     */
    public function getActiveResolutionMinutes(): Collection
    {
        return DB::table('sla_policies')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->pluck('resolution_minutes', 'priority');
    }
}

<?php

namespace App\Services\V1\Assignment;

interface AutoAssignmentStrategy
{
    public function pickAgent(int $categoryId): ?int;
}

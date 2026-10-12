<?php

namespace App\Services\V1\Assignment;

use App\Repositories\V1\UserRepositoryV1;

class LeastLoadedAssignmentStrategy implements AutoAssignmentStrategy
{
    public function __construct(private readonly UserRepositoryV1 $userRepositoryV1) {}

    public function pickAgent(int $categoryId): ?int
    {
        return $this->userRepositoryV1->setCategoryId($categoryId)->getLeastLoadedAgentId();
    }
}

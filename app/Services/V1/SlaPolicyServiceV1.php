<?php

namespace App\Services\V1;

use App\Repositories\V1\SlaPolicyRepositoryV1;

class SlaPolicyServiceV1
{
    public function __construct(private readonly SlaPolicyRepositoryV1 $slaPolicyRepositoryV1
    ) {}


}

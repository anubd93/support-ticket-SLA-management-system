<?php

namespace App\Services\V1;

use App\Repositories\V1\BusinessHourRepositoryV1;

class BusinessHourServiceV1
{
    public function __construct(private readonly BusinessHourRepositoryV1 $businessHourRepositoryV1
    ) {}


}

<?php

namespace App\Services\V1;

use App\Repositories\V1\BusinessHourRepositoryV1;
use App\Repositories\V1\HolidayRepositoryV1;
use App\Repositories\V1\SlaPolicyRepositoryV1;

class SlaCalculatorServiceV1
{
    public function __construct(private readonly SlaPolicyRepositoryV1 $slaPolicyRepositoryV1,
                                private readonly BusinessHourRepositoryV1 $businessHourRepositoryV1,
                                private readonly HolidayRepositoryV1 $holidayRepositoryV1
    ) {}


}

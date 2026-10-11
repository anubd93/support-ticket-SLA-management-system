<?php

namespace App\Services\V1;

use App\Repositories\V1\HolidayRepositoryV1;

class HolidayServiceV1
{
    public function __construct(private readonly HolidayRepositoryV1 $holidayRepositoryV1
    ) {}


}

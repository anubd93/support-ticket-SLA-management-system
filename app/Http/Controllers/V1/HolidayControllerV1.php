<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\HolidayServiceV1;

class HolidayControllerV1 extends Controller
{
    public function __construct(private readonly HolidayServiceV1 $holidayServiceV1) {}


}

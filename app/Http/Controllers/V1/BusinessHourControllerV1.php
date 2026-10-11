<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\BusinessHourServiceV1;

class BusinessHourControllerV1 extends Controller
{
    public function __construct(private readonly BusinessHourServiceV1 $businessHourServiceV1) {}


}

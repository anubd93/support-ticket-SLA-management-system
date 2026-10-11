<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\DashboardServiceV1;

class DashboardControllerV1 extends Controller
{
    public function __construct(private readonly DashboardServiceV1 $dashboardServiceV1) {}


}

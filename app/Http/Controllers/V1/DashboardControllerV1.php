<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\DashboardServiceV1;
use Illuminate\Contracts\View\View;

class DashboardControllerV1 extends Controller
{
    public function __construct(private readonly DashboardServiceV1 $dashboardServiceV1) {}

    public function index(): View
    {
        return view('dashboard.index');
    }
}

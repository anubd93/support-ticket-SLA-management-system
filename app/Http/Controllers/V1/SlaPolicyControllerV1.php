<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\SlaPolicyServiceV1;

class SlaPolicyControllerV1 extends Controller
{
    public function __construct(private readonly SlaPolicyServiceV1 $slaPolicyServiceV1) {}


}

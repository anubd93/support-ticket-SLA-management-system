<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\RoleServiceV1;

class RoleControllerV1 extends Controller
{
    public function __construct(private readonly RoleServiceV1 $roleServiceV1) {}


}

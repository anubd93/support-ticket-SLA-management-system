<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\UserServiceV1;

class UserControllerV1 extends Controller
{
    public function __construct(private readonly UserServiceV1 $userServiceV1) {}


}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\AuthServiceV1;

class AuthControllerV1 extends Controller
{
    public function __construct(private readonly AuthServiceV1 $authServiceV1) {}


}

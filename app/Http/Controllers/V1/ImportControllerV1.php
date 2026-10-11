<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\ImportServiceV1;

class ImportControllerV1 extends Controller
{
    public function __construct(private readonly ImportServiceV1 $importServiceV1) {}


}

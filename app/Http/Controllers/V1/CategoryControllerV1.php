<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\CategoryServiceV1;

class CategoryControllerV1 extends Controller
{
    public function __construct(private readonly CategoryServiceV1 $categoryServiceV1) {}


}

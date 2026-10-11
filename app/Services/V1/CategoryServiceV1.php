<?php

namespace App\Services\V1;

use App\Repositories\V1\CategoryRepositoryV1;

class CategoryServiceV1
{
    public function __construct(private readonly CategoryRepositoryV1 $categoryRepositoryV1
    ) {}


}

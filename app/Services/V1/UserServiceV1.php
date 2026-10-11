<?php

namespace App\Services\V1;

use App\Repositories\V1\CategoryRepositoryV1;
use App\Repositories\V1\RoleRepositoryV1;
use App\Repositories\V1\UserRepositoryV1;

class UserServiceV1
{
    public function __construct(private readonly UserRepositoryV1 $userRepositoryV1,
                                private readonly RoleRepositoryV1 $roleRepositoryV1,
                                private readonly CategoryRepositoryV1 $categoryRepositoryV1
    ) {}


}

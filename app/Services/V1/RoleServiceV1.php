<?php

namespace App\Services\V1;

use App\Repositories\V1\PermissionRepositoryV1;
use App\Repositories\V1\RoleRepositoryV1;

class RoleServiceV1
{
    public function __construct(private readonly RoleRepositoryV1 $roleRepositoryV1,
                                private readonly PermissionRepositoryV1 $permissionRepositoryV1
    ) {}


}

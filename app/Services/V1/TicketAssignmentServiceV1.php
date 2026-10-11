<?php

namespace App\Services\V1;

use App\Repositories\V1\TicketActivityRepositoryV1;
use App\Repositories\V1\TicketRepositoryV1;
use App\Repositories\V1\UserRepositoryV1;

class TicketAssignmentServiceV1
{
    public function __construct(private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly UserRepositoryV1 $userRepositoryV1,
                                private readonly TicketActivityRepositoryV1 $ticketActivityRepositoryV1
    ) {}


}

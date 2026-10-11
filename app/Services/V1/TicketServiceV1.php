<?php

namespace App\Services\V1;

use App\Repositories\V1\CategoryRepositoryV1;
use App\Repositories\V1\TicketActivityRepositoryV1;
use App\Repositories\V1\TicketRepositoryV1;

class TicketServiceV1
{
    public function __construct(private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly CategoryRepositoryV1 $categoryRepositoryV1,
                                private readonly TicketActivityRepositoryV1 $ticketActivityRepositoryV1
    ) {}


}

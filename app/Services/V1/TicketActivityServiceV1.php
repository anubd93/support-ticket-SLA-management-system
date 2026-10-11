<?php

namespace App\Services\V1;

use App\Repositories\V1\TicketActivityRepositoryV1;

class TicketActivityServiceV1
{
    public function __construct(private readonly TicketActivityRepositoryV1 $ticketActivityRepositoryV1
    ) {}


}

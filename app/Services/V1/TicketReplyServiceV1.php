<?php

namespace App\Services\V1;

use App\Repositories\V1\TicketActivityRepositoryV1;
use App\Repositories\V1\TicketReplyRepositoryV1;
use App\Repositories\V1\TicketRepositoryV1;

class TicketReplyServiceV1
{
    public function __construct(private readonly TicketReplyRepositoryV1 $ticketReplyRepositoryV1,
                                private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly TicketActivityRepositoryV1 $ticketActivityRepositoryV1
    ) {}


}

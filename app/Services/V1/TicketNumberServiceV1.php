<?php

namespace App\Services\V1;

use App\Repositories\V1\TicketSequenceRepositoryV1;

class TicketNumberServiceV1
{
    public function __construct(private readonly TicketSequenceRepositoryV1 $ticketSequenceRepositoryV1
    ) {}


}

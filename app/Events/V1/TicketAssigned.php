<?php

namespace App\Events\V1;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class TicketAssigned implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $ticketId,
        public int $agentId,
        public ?int $actorId,
    ) {}
}

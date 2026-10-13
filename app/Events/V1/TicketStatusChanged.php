<?php

namespace App\Events\V1;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class TicketStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $ticketId,
        public string $from,
        public string $to,
        public int $actorId,
    ) {}
}

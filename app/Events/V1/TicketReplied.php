<?php

namespace App\Events\V1;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class TicketReplied implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(
        public int $ticketId,
        public int $replyId,
        public int $actorId,
        public bool $isInternal,
    ) {}
}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\TicketReplyServiceV1;

class TicketReplyControllerV1 extends Controller
{
    public function __construct(private readonly TicketReplyServiceV1 $ticketReplyServiceV1) {}


}

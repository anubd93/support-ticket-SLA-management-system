<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\TicketServiceV1;

class TicketControllerV1 extends Controller
{
    public function __construct(private readonly TicketServiceV1 $ticketServiceV1) {}


}

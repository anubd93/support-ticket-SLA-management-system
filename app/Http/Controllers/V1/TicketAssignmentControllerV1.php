<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\TicketAssignmentServiceV1;

class TicketAssignmentControllerV1 extends Controller
{
    public function __construct(private readonly TicketAssignmentServiceV1 $ticketAssignmentServiceV1) {}


}

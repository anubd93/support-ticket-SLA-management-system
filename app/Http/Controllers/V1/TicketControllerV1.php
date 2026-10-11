<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreTicketRequestV1;
use App\Http\Requests\V1\UpdateTicketRequestV1;
use App\Http\Requests\V1\UpdateTicketStatusRequestV1;
use App\Services\V1\TicketServiceV1;
use Illuminate\Contracts\View\View;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TicketControllerV1 extends Controller
{
    public function __construct(private readonly TicketServiceV1 $ticketServiceV1) {}

    public function index(): View
    {
        return view('tickets.index', [
            'tickets' => $this->ticketServiceV1->getActiveTickets(),
            'counts' => $this->ticketServiceV1->getTabCounts(),
        ]);
    }

    public function resolved(): View
    {
        return view('tickets.resolved', [
            'tickets' => $this->ticketServiceV1->getFinishedTickets(),
            'counts' => $this->ticketServiceV1->getTabCounts(),
        ]);
    }

    public function create(): View
    {
        return view('tickets.create', $this->ticketServiceV1->getFormOptions());
    }

    public function store(StoreTicketRequestV1 $request): JsonResponse
    {
        try {
            $ticket = $this->ticketServiceV1->create($request->validated(), $request->user()->id);

            return response()->json([
                'message' => "Ticket {$ticket->ticket_number} created.",
                'redirect' => route('tickets.show', $ticket->id),
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): View
    {
        $ticket = $this->ticketServiceV1->findTicket($id);
        abort_if(! $ticket, Response::HTTP_NOT_FOUND);

        return view('tickets.show', [
            'ticket' => $ticket,
            'activities' => $this->ticketServiceV1->getActivities($id),
            'sla' => $this->ticketServiceV1->getSlaSummary($ticket),
        ]);
    }

    public function edit(int $id): View
    {
        $ticket = $this->ticketServiceV1->findTicket($id);
        abort_if(! $ticket, Response::HTTP_NOT_FOUND);

        return view('tickets.edit', ['ticket' => $ticket] + $this->ticketServiceV1->getFormOptions());
    }

    public function update(UpdateTicketRequestV1 $request, int $id): JsonResponse
    {
        try {
            $this->ticketServiceV1->update($id, $request->validated(), $request->user()->id);

            return response()->json([
                'message' => 'Ticket updated.',
                'redirect' => route('tickets.show', $id),
            ]);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function updateStatus(UpdateTicketStatusRequestV1 $request, int $id): JsonResponse
    {
        try {
            $ticket = $this->ticketServiceV1->changeStatus($id, $request->validated('status'), $request->user()->id);

            return response()->json([
                'message' => "Status changed to {$ticket->status->label()}.",
            ]);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}

<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreTicketRequestV1;
use App\Http\Requests\V1\TicketFilterRequestV1;
use App\Http\Requests\V1\UpdateTicketRequestV1;
use App\Http\Requests\V1\UpdateTicketStatusRequestV1;
use App\Models\V1\Ticket;
use App\Services\V1\AttachmentServiceV1;
use App\Services\V1\TicketAssignmentServiceV1;
use App\Services\V1\TicketReplyServiceV1;
use App\Services\V1\TicketServiceV1;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class TicketControllerV1 extends Controller
{
    public function __construct(private readonly TicketServiceV1 $ticketServiceV1,
                                private readonly TicketAssignmentServiceV1 $ticketAssignmentServiceV1,
                                private readonly TicketReplyServiceV1 $ticketReplyServiceV1,
                                private readonly AttachmentServiceV1 $attachmentServiceV1
    ) {}

    public function index(TicketFilterRequestV1 $request): View
    {
        $filters = $request->validated();

        return view('tickets.index', [
            'tickets' => $this->ticketServiceV1->getActiveTickets($request->user(), $filters),
            'counts' => $this->ticketServiceV1->getTabCounts($request->user()),
            'filters' => $filters,
            'filterOptions' => $this->ticketServiceV1->getFilterOptions($request->user(), 'active'),
        ]);
    }

    public function unassigned(TicketFilterRequestV1 $request): View
    {
        abort_unless(Gate::any(['ticket.claim', 'ticket.assign']), Response::HTTP_FORBIDDEN);

        $filters = $request->validated();

        return view('tickets.index', [
            'tickets' => $this->ticketServiceV1->getUnassignedTickets($request->user(), $filters),
            'counts' => $this->ticketServiceV1->getTabCounts($request->user()),
            'filters' => $filters,
            'filterOptions' => $this->ticketServiceV1->getFilterOptions($request->user(), 'unassigned'),
        ]);
    }

    public function queue(TicketFilterRequestV1 $request): View
    {
        Gate::authorize('ticket.claim');

        $filters = $request->validated();

        return view('tickets.index', [
            'tickets' => $this->ticketServiceV1->getMyQueue($request->user(), $filters),
            'counts' => $this->ticketServiceV1->getTabCounts($request->user()),
            'filters' => $filters,
            'filterOptions' => $this->ticketServiceV1->getFilterOptions($request->user(), 'queue'),
        ]);
    }

    public function resolved(TicketFilterRequestV1 $request): View
    {
        $filters = $request->validated();

        return view('tickets.resolved', [
            'tickets' => $this->ticketServiceV1->getFinishedTickets($request->user(), $filters),
            'counts' => $this->ticketServiceV1->getTabCounts($request->user()),
            'filters' => $filters,
            'filterOptions' => $this->ticketServiceV1->getFilterOptions($request->user(), 'finished'),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('ticket.create');

        return view('tickets.create', $this->ticketServiceV1->getFormOptions());
    }

    public function store(StoreTicketRequestV1 $request): JsonResponse
    {
        Gate::authorize('ticket.create');

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

    public function show(Request $request, int $id): View
    {
        $ticket = $this->findOrFail($id);
        Gate::authorize('view', [Ticket::class, $ticket]);

        return view('tickets.show', [
            'ticket' => $ticket,
            'replies' => $this->ticketReplyServiceV1->getThread($id, $request->user()),
            'attachments' => $this->attachmentServiceV1->getForTicket($id, $request->user()),
            'activities' => $this->ticketServiceV1->getActivities($id, $request->user()),
            'sla' => $this->ticketServiceV1->getSlaSummary($ticket),
            'agents' => Gate::allows('assign', [Ticket::class, $ticket])
                ? $this->ticketAssignmentServiceV1->getAgentOptions()
                : collect(),
        ]);
    }

    public function edit(int $id): View
    {
        $ticket = $this->findOrFail($id);
        Gate::authorize('update', [Ticket::class, $ticket]);

        return view('tickets.edit', ['ticket' => $ticket] + $this->ticketServiceV1->getFormOptions());
    }

    public function update(UpdateTicketRequestV1 $request, int $id): JsonResponse
    {
        Gate::authorize('update', [Ticket::class, $this->findOrFail($id)]);

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
        Gate::authorize('changeStatus', [Ticket::class, $this->findOrFail($id)]);

        try {
            $ticket = $this->ticketServiceV1->changeStatus($id, $request->validated('status'), $request->user()->id);

            return response()->json([
                'message' => "Status changed to {$ticket->status->label()}.",
            ]);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function findOrFail(int $id): object
    {
        $ticket = $this->ticketServiceV1->findTicket($id);
        abort_if(! $ticket, Response::HTTP_NOT_FOUND);

        return $ticket;
    }
}

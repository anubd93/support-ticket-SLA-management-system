<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AssignTicketRequestV1;
use App\Models\V1\Ticket;
use App\Services\V1\TicketAssignmentServiceV1;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class TicketAssignmentControllerV1 extends Controller
{
    public function __construct(private readonly TicketAssignmentServiceV1 $ticketAssignmentServiceV1) {}

    public function assign(AssignTicketRequestV1 $request, int $id): JsonResponse
    {
        Gate::authorize('assign', [Ticket::class, $this->findOrFail($id)]);

        try {
            $agentId = $request->validated('agent_id');

            $this->ticketAssignmentServiceV1->assign($id, $agentId ? (int) $agentId : null, $request->user()->id);

            return response()->json([
                'message' => $agentId ? 'Ticket assigned.' : 'Ticket unassigned.',
            ]);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function claim(Request $request, int $id): JsonResponse
    {
        Gate::authorize('claim', [Ticket::class, $this->findOrFail($id)]);

        try {
            $this->ticketAssignmentServiceV1->claim($id, $request->user()->id);

            return response()->json([
                'message' => 'You accepted this ticket.',
                'redirect' => route('tickets.show', $id),
            ]);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function findOrFail(int $id): object
    {
        $ticket = $this->ticketAssignmentServiceV1->findTicket($id);
        abort_if(! $ticket, Response::HTTP_NOT_FOUND);

        return $ticket;
    }
}

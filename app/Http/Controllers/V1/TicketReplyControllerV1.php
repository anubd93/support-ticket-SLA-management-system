<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreTicketReplyRequestV1;
use App\Models\V1\Ticket;
use App\Services\V1\TicketReplyServiceV1;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class TicketReplyControllerV1 extends Controller
{
    public function __construct(private readonly TicketReplyServiceV1 $ticketReplyServiceV1) {}

    public function store(StoreTicketReplyRequestV1 $request, int $id): JsonResponse
    {
        $ticket = $this->findOrFail($id);
        Gate::authorize('reply', [Ticket::class, $ticket]);

        if ($request->boolean('is_internal')) {
            Gate::authorize('addInternalNote', [Ticket::class, $ticket]);
        }

        try {
            $result = $this->ticketReplyServiceV1->create($id, $request->validated(), $request->user());

            return response()->json([
                'message' => $result['reply']->is_internal ? 'Internal note added.' : 'Reply sent.',
                'html' => view('tickets.partials.reply-item', ['reply' => $result['reply']])->render(),
                'reload' => $result['status_changed'],
            ], Response::HTTP_CREATED);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function findOrFail(int $id): object
    {
        $ticket = $this->ticketReplyServiceV1->findTicket($id);
        abort_if(! $ticket, Response::HTTP_NOT_FOUND);

        return $ticket;
    }
}

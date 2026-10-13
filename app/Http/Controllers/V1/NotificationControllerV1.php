<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Services\V1\NotificationServiceV1;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotificationControllerV1 extends Controller
{
    public function __construct(private readonly NotificationServiceV1 $notificationServiceV1) {}

    public function open(Request $request, string $id): RedirectResponse
    {
        $ticketId = $this->notificationServiceV1->open($request->user(), $id);
        abort_if(! $ticketId, Response::HTTP_NOT_FOUND);

        return redirect()->route('tickets.show', $ticketId);
    }

    public function readAll(Request $request): JsonResponse
    {
        try {
            $this->notificationServiceV1->markAllAsRead($request->user());

            return response()->json(['message' => 'All notifications marked as read.']);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}

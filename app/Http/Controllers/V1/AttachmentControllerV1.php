<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\V1\Ticket;
use App\Services\V1\AttachmentServiceV1;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentControllerV1 extends Controller
{
    public function __construct(private readonly AttachmentServiceV1 $attachmentServiceV1) {}

    public function download(int $id): StreamedResponse
    {
        $attachment = $this->attachmentServiceV1->findForDownload($id);
        abort_if(! $attachment, Response::HTTP_NOT_FOUND);

        Gate::authorize('download', [Ticket::class, $attachment]);

        return $this->attachmentServiceV1->download($attachment);
    }
}

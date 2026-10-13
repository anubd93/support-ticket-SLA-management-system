<?php

namespace App\Services\V1;

use App\Models\User;
use App\Repositories\V1\AttachmentRepositoryV1;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class AttachmentServiceV1
{
    public function __construct(private readonly AttachmentRepositoryV1 $attachmentRepositoryV1
    ) {}

    public function storeFiles(array $files): array
    {
        $disk = config('tickets.attachments.disk');
        $stored = [];

        try {
            foreach ($files as $file) {
                $name = Str::uuid() . '.' . ($file->extension() ?: 'bin');
                $path = $file->storeAs('attachments/' . now()->format('Y/m'), $name, $disk);

                if ($path === false) {
                    throw new Exception('Could not save "' . $file->getClientOriginalName() . '". Please try again.');
                }

                $stored[] = [
                    'disk' => $disk,
                    'path' => $path,
                    'original_name' => Str::limit(basename($file->getClientOriginalName()), 250, ''),
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'size' => $file->getSize(),
                ];
            }
        } catch (Throwable $exception) {
            $this->discard($stored);
            throw $exception;
        }

        return $stored;
    }

    public function saveRecords(int $ticketId, ?int $replyId, array $stored, int $userId): array
    {
        return array_map(fn (array $file) => $this->attachmentRepositoryV1
            ->setTicketId($ticketId)
            ->setTicketReplyId($replyId)
            ->setUploadedBy($userId)
            ->setDisk($file['disk'])
            ->setPath($file['path'])
            ->setOriginalName($file['original_name'])
            ->setMimeType($file['mime_type'])
            ->setSize($file['size'])
            ->store(), $stored);
    }

    public function discard(array $stored): void
    {
        foreach ($stored as $file) {
            Storage::disk($file['disk'])->delete($file['path']);
        }
    }
    
    public function getForTicket(int $ticketId, User $user): Collection
    {
        return $this->attachmentRepositoryV1
            ->setTicketId($ticketId)
            ->setIncludeInternal($user->hasPermission('ticket.internal_note'))
            ->getByTicketId()
            ->groupBy(fn ($attachment) => $attachment->ticket_reply_id ?? 'ticket');
    }

    public function findForDownload(int $id): ?object
    {
        return $this->attachmentRepositoryV1->setId($id)->findForDownload();
    }

    /**
     * Streams the file as a download (Content-Disposition: attachment), never rendered inline.
     */
    public function download(object $attachment): StreamedResponse
    {
        $disk = Storage::disk($attachment->disk);

        abort_unless($disk->exists($attachment->path), StreamedResponse::HTTP_NOT_FOUND);

        // nosniff: the browser must trust our Content-Type and never guess (e.g. treat a .txt as HTML).
        return $disk->download($attachment->path, $attachment->original_name, ['X-Content-Type-Options' => 'nosniff']);
    }
}

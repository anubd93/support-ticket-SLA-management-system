<?php

namespace App\Repositories\V1;

use App\Models\V1\Attachment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttachmentRepositoryV1
{
    private $id, $ticket_id, $ticket_reply_id, $uploaded_by, $disk, $path,
        $original_name, $mime_type, $size, $created_at;

    // false = customers: files on internal notes are filtered out in SQL.
    private bool $include_internal = true;

    public function setIncludeInternal(bool $include_internal)
    {
        $this->include_internal = $include_internal;
        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setTicketId($ticket_id)
    {
        $this->ticket_id = $ticket_id;
        return $this;
    }

    public function setTicketReplyId($ticket_reply_id)
    {
        $this->ticket_reply_id = $ticket_reply_id;
        return $this;
    }

    public function setUploadedBy($uploaded_by)
    {
        $this->uploaded_by = $uploaded_by;
        return $this;
    }

    public function setDisk($disk)
    {
        $this->disk = $disk;
        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function setOriginalName($original_name)
    {
        $this->original_name = $original_name;
        return $this;
    }

    public function setMimeType($mime_type)
    {
        $this->mime_type = $mime_type;
        return $this;
    }

    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function store(): Attachment
    {
        return Attachment::create([
            'ticket_id' => $this->ticket_id,
            'ticket_reply_id' => $this->ticket_reply_id,
            'uploaded_by' => $this->uploaded_by,
            'disk' => $this->disk,
            'path' => $this->path,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
        ]);
    }

    public function getByTicketId(): Collection
    {
        return DB::table('attachments as a')
            ->leftJoin('ticket_replies as r', 'r.id', '=', 'a.ticket_reply_id')
            ->where('a.ticket_id', $this->ticket_id)
            ->whereNull('a.deleted_at')
            ->when(! $this->include_internal, function ($query) {
                $query->where(fn ($query) => $query->whereNull('a.ticket_reply_id')->orWhere('r.is_internal', false));
            })
            ->select('a.id', 'a.ticket_reply_id', 'a.original_name', 'a.mime_type', 'a.size')
            ->orderBy('a.id')
            ->get();
    }

    public function findForDownload(): ?object
    {
        return DB::table('attachments as a')
            ->join('tickets as t', 't.id', '=', 'a.ticket_id')
            ->leftJoin('ticket_replies as r', 'r.id', '=', 'a.ticket_reply_id')
            ->where('a.id', $this->id)
            ->whereNull('a.deleted_at')
            ->whereNull('t.deleted_at')
            ->select(
                'a.id',
                'a.disk',
                'a.path',
                'a.original_name',
                'a.mime_type',
                'a.ticket_id',
                't.created_by',
                't.status',
                'r.is_internal',
            )
            ->first();
    }
}

<?php

namespace App\Repositories\V1;

class AttachmentRepositoryV1
{
    private $id, $ticket_id, $ticket_reply_id, $uploaded_by, $disk, $path,
        $original_name, $mime_type, $size, $created_at;

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
}

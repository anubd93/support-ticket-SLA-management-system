<?php

namespace App\Repositories\V1;

class TicketActivityRepositoryV1
{
    private $id, $ticket_id, $user_id, $type, $old_values, $new_values, $created_at;

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

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function setOldValues($old_values)
    {
        $this->old_values = $old_values;
        return $this;
    }

    public function setNewValues($new_values)
    {
        $this->new_values = $new_values;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }
}

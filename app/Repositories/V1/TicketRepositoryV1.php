<?php

namespace App\Repositories\V1;

class TicketRepositoryV1
{
    private $id, $ticket_number, $subject, $description, $priority, $status, $source,
        $external_reference, $category_id, $created_by, $assigned_to, $assigned_at,
        $sla_due_at, $sla_paused_at, $sla_paused_minutes, $first_responded_at,
        $sla_warning_sent_at, $sla_breached_at, $escalated_at, $escalation_level,
        $resolved_at, $closed_at, $lock_version, $created_at;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setTicketNumber($ticket_number)
    {
        $this->ticket_number = $ticket_number;
        return $this;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    public function setExternalReference($external_reference)
    {
        $this->external_reference = $external_reference;
        return $this;
    }

    public function setCategoryId($category_id)
    {
        $this->category_id = $category_id;
        return $this;
    }

    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
        return $this;
    }

    public function setAssignedTo($assigned_to)
    {
        $this->assigned_to = $assigned_to;
        return $this;
    }

    public function setAssignedAt($assigned_at)
    {
        $this->assigned_at = $assigned_at;
        return $this;
    }

    public function setSlaDueAt($sla_due_at)
    {
        $this->sla_due_at = $sla_due_at;
        return $this;
    }

    public function setSlaPausedAt($sla_paused_at)
    {
        $this->sla_paused_at = $sla_paused_at;
        return $this;
    }

    public function setSlaPausedMinutes($sla_paused_minutes)
    {
        $this->sla_paused_minutes = $sla_paused_minutes;
        return $this;
    }

    public function setFirstRespondedAt($first_responded_at)
    {
        $this->first_responded_at = $first_responded_at;
        return $this;
    }

    public function setSlaWarningSentAt($sla_warning_sent_at)
    {
        $this->sla_warning_sent_at = $sla_warning_sent_at;
        return $this;
    }

    public function setSlaBreachedAt($sla_breached_at)
    {
        $this->sla_breached_at = $sla_breached_at;
        return $this;
    }

    public function setEscalatedAt($escalated_at)
    {
        $this->escalated_at = $escalated_at;
        return $this;
    }

    public function setEscalationLevel($escalation_level)
    {
        $this->escalation_level = $escalation_level;
        return $this;
    }

    public function setResolvedAt($resolved_at)
    {
        $this->resolved_at = $resolved_at;
        return $this;
    }

    public function setClosedAt($closed_at)
    {
        $this->closed_at = $closed_at;
        return $this;
    }

    public function setLockVersion($lock_version)
    {
        $this->lock_version = $lock_version;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }
}

<?php

namespace App\Enums\V1;

/**
 * Values stored in ticket_activities.type.
 */
enum ActivityType: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Assigned = 'assigned';
    case Unassigned = 'unassigned';
    case StatusChanged = 'status_changed';
    case PriorityChanged = 'priority_changed';
    case CategoryChanged = 'category_changed';
    case Replied = 'replied';
    case InternalNote = 'internal_note';
    case AttachmentAdded = 'attachment_added';
    case SlaWarning = 'sla_warning';
    case SlaBreached = 'sla_breached';
    case Escalated = 'escalated';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Ticket created',
            self::Updated => 'Ticket updated',
            self::Assigned => 'Assigned',
            self::Unassigned => 'Unassigned',
            self::StatusChanged => 'Status changed',
            self::PriorityChanged => 'Priority changed',
            self::CategoryChanged => 'Category changed',
            self::Replied => 'Replied',
            self::InternalNote => 'Internal note added',
            self::AttachmentAdded => 'Attachment added',
            self::SlaWarning => 'SLA approaching',
            self::SlaBreached => 'SLA breached',
            self::Escalated => 'Escalated',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Reopened => 'Reopened',
        };
    }
}

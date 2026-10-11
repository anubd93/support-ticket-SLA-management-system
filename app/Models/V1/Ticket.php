<?php

namespace App\Models\V1;

use App\Enums\V1\TicketPriority;
use App\Enums\V1\TicketSource;
use App\Enums\V1\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'tickets';

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'source' => TicketSource::class,
            'assigned_at' => 'datetime',
            'sla_due_at' => 'datetime',
            'sla_warning_at' => 'datetime',
            'sla_paused_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'sla_warning_sent_at' => 'datetime',
            'sla_breached_at' => 'datetime',
            'escalated_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models\V1;

use App\Enums\V1\ActivityType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketActivity extends Model
{
    use HasFactory, SoftDeletes;
    
    const UPDATED_AT = null;

    protected $guarded = [];

    protected $table = 'ticket_activities';

    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Who did it; null = the system (auto-assignment, SLA escalation).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

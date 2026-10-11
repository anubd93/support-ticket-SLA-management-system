<?php

namespace App\Models\V1;

use App\Enums\V1\ActivityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketActivity extends Model
{
    use HasFactory, SoftDeletes;

    // The table has no updated_at column: activity rows are never updated.
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
}

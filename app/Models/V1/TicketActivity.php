<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketActivity extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'ticket_activities';
}

<?php

namespace App\Models\V1;

use App\Enums\V1\ImportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Import extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'imports';

    protected function casts(): array
    {
        return [
            'status' => ImportStatus::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}

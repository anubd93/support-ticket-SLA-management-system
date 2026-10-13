<?php

namespace App\Models\V1;

use App\Enums\V1\ImportStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function errors(): HasMany
    {
        return $this->hasMany(ImportError::class);
    }
}

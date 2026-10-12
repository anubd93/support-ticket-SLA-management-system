<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportError extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'import_errors';

    protected function casts(): array
    {
        return [
            'row_data' => 'array',
            'errors' => 'array',
        ];
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(Import::class);
    }
}

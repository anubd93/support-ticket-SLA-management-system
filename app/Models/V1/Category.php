<?php

namespace App\Models\V1;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'categories';

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
    
    public function agents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'category_user')->wherePivotNull('deleted_at');
    }
}

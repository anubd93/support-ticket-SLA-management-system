<?php

namespace App\Models\V1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $table = 'permissions';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')->wherePivotNull('deleted_at');
    }
}

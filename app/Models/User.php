<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\V1\UserRole;
use App\Models\V1\Category;
use App\Models\V1\Role;
use App\Models\V1\Ticket;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

#[Fillable(['role_id', 'name', 'email', 'password', 'is_active', 'is_available'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    private ?array $permissionNames = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_available' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_user')->wherePivotNull('deleted_at');
    }
    
    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }
    
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function hasPermission(string $permission): bool
    {
        $this->permissionNames ??= $this->role?->permissions()->pluck('name')->all() ?? [];

        return in_array($permission, $this->permissionNames, true);
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === UserRole::Admin->value;
    }

    public function isAgent(): bool
    {
        return $this->role?->name === UserRole::Agent->value;
    }

    public function isCustomer(): bool
    {
        return $this->role?->name === UserRole::Customer->value;
    }
}

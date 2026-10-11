<?php

namespace App\Providers;

use App\Models\User;
use App\Models\V1\Ticket;
use App\Policies\V1\TicketPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Every permission name works as an ability:
        // Gate::allows('settings.manage'), @can('ticket.create'), middleware('can:import.run').
        // Returning null (not false) lets policies decide abilities like 'view' and 'update'.
        Gate::before(fn (User $user, string $ability) => $user->hasPermission($ability) ? true : null);

        Gate::policy(Ticket::class, TicketPolicy::class);
    }
}

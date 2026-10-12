<?php

namespace App\Providers;

use App\Models\User;
use App\Models\V1\Ticket;
use App\Policies\V1\TicketPolicy;
use App\Services\V1\Assignment\AutoAssignmentStrategy;
use App\Services\V1\Assignment\LeastLoadedAssignmentStrategy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Swap the auto-assignment rule here (e.g. pure round-robin) without touching TicketServiceV1.
        $this->app->bind(AutoAssignmentStrategy::class, LeastLoadedAssignmentStrategy::class);
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

<?php

namespace App\Providers;

use App\Models\User;
use App\Models\V1\Ticket;
use App\Policies\V1\TicketPolicy;
use App\Services\V1\Assignment\AutoAssignmentStrategy;
use App\Services\V1\Assignment\LeastLoadedAssignmentStrategy;
use App\Services\V1\NotificationServiceV1;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AutoAssignmentStrategy::class, LeastLoadedAssignmentStrategy::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(fn (User $user, string $ability) => $user->hasPermission($ability) ? true : null);

        Gate::policy(Ticket::class, TicketPolicy::class);
        
        View::composer('layouts.partials.topbar', function ($view) {
            $view->with(app(NotificationServiceV1::class)->forBell(auth()->user()));
        });
    }
}

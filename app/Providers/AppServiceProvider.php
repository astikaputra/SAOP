<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define Gates based on roles
        Gate::define('is-admin', function ($user) {
            return in_array($user->role, ['SUPER_ADMIN', 'ADMIN']);
        });

        Gate::define('is-loket-staff', function ($user) {
            return $user->role === 'LOKET_STAFF';
        });

        Gate::define('is-driver', function ($user) {
            return $user->role === 'DRIVER';
        });

        Gate::define('is-manager', function ($user) {
            return $user->role === 'MANAGER';
        });

        Gate::define('access-admin', function ($user) {
            return in_array($user->role, ['SUPER_ADMIN', 'ADMIN', 'MANAGER']);
        });
    }
}
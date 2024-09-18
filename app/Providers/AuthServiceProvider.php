<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    // /**
    //  * Register any authentication / authorization services.
    //  *
    //  * @return void
    //  */
    // public function register()
    // {
    //     // No Passport::routes() here
    //     Passport::ignoreRoutes(); // Optional: Disable default Passport routes
    // }

    /**
     * Bootstrap any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        // Passport::ignoreRoutes();
            
    }
}

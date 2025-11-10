<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        // Passport routes will be registered in routes/api.php
        // Passport::routes();

        // Expiration tokens
        // Passport::tokensExpireIn(Carbon::now()->addHour()); // access token
        // Passport::refreshTokensExpireIn(Carbon::now()->addDays(30)); // refresh token
    }
}

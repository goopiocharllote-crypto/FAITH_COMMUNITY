<?php

namespace App\Providers;


use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
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
        View::addNamespace('user-components', resource_path('users/components'));

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}

<?php

namespace App\Providers;

use App\Payments\Gateways\PayMongoGateway;
use App\Payments\Gateways\SimulatedGateway;
use App\Payments\PaymentGateway;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, fn () => match (config('payments.gateway')) {
            'paymongo' => new PayMongoGateway(
                config('payments.paymongo.secret_key'),
                config('payments.paymongo.base_url'),
            ),
            default => new SimulatedGateway,
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::addNamespace('user-components', resource_path('users/components'));
    }
}

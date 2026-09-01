<?php

namespace App\Domain\Billing;

use App\Domain\Billing\Contracts\BillingGateway;
use App\Domain\Billing\Services\StripeBillingGateway;
use Illuminate\Support\ServiceProvider;

class BillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('domains/billing.php'), 'domains.billing');

        $this->app->singleton(BillingManager::class, function ($app) {
            return new BillingManager($app);
        });

        $this->app->bind(BillingGateway::class, function ($app) {
            return $app->make(BillingManager::class);
        });
    }

    public function boot(): void
    {
        // Central routes (e.g. Stripe Webhooks)
        $this->loadRoutesFrom(__DIR__.'/routes/central.php');
    }
}

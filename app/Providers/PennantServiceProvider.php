<?php

namespace App\Providers;

use App\Domain\Billing\Models\Plan;
use App\Models\Tenant;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class PennantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Scope feature checks by default to active tenant
        Feature::resolveScopeUsing(fn () => tenant());

        // Define plan-based features
        Feature::define('team-invites', function (?Tenant $tenant = null) {
            $tenant = $tenant ?? tenant();
            if (! $tenant) {
                return false;
            }

            if ($tenant->onTrial()) {
                return true;
            }

            $plan = Plan::where('slug', $tenant->plan)->first();

            return $plan ? $plan->hasFeature('team-invites') : false;
        });

        Feature::define('advanced-analytics', function (?Tenant $tenant = null) {
            $tenant = $tenant ?? tenant();
            if (! $tenant) {
                return false;
            }

            if ($tenant->onTrial()) {
                return true;
            }

            $plan = Plan::where('slug', $tenant->plan)->first();

            return $plan ? $plan->hasFeature('advanced-analytics') : false;
        });

        Feature::define('custom-branding', function (?Tenant $tenant = null) {
            $tenant = $tenant ?? tenant();
            if (! $tenant) {
                return false;
            }

            if ($tenant->onTrial()) {
                return true;
            }

            $plan = Plan::where('slug', $tenant->plan)->first();

            return $plan ? $plan->hasFeature('custom-branding') : false;
        });
    }
}

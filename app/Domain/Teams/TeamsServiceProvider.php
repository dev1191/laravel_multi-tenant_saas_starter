<?php

namespace App\Domain\Teams;

use Illuminate\Support\ServiceProvider;

class TeamsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(config_path('domains/teams.php'), 'domains.teams');
    }

    public function boot(): void
    {
        // Tenant routes are registered via routes/tenant.php or domain routes
    }
}

<?php

use App\Domain\Activity\ActivityServiceProvider;
use App\Domain\Billing\BillingServiceProvider;
use App\Domain\Settings\SettingsServiceProvider;
use App\Domain\Tasks\TasksServiceProvider;
use App\Domain\Teams\TeamsServiceProvider;
use App\Domain\TenantAdmin\TenantAdminServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\PennantServiceProvider;
use App\Providers\TenancyServiceProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    FortifyServiceProvider::class,
    PennantServiceProvider::class,
    TenancyServiceProvider::class,

    // Modular Domain Service Providers
    BillingServiceProvider::class,
    TeamsServiceProvider::class,
    TenantAdminServiceProvider::class,
    TasksServiceProvider::class,
    SettingsServiceProvider::class,
    ActivityServiceProvider::class,
];

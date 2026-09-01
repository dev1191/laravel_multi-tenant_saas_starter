<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Middleware\TagImpersonatedActivity;
use App\Http\Middleware\TenantAccessStatus;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Tenant routes identified by domain or subdomain, aggregating modular domain routes.
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
    TagImpersonatedActivity::class,
    TenantAccessStatus::class,
])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

    // In-App Notifications
    Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/notifications', [\App\Http\Controllers\Tenant\NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Tenant\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [\App\Http\Controllers\Tenant\NotificationController::class, 'markAllAsRead'])->name('notifications.read_all');
        Route::delete('/notifications/{id}', [\App\Http\Controllers\Tenant\NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // Modular Domain Tenant Routes
    require base_path('app/Domain/TenantAdmin/routes/tenant.php');
    require base_path('app/Domain/Teams/routes/tenant.php');
    require base_path('app/Domain/Tasks/routes/tenant.php');
    require base_path('app/Domain/Billing/routes/tenant.php');
    require base_path('app/Domain/Settings/routes/tenant.php');
    require base_path('app/Domain/Activity/routes/tenant.php');
    require base_path('routes/settings.php');
});

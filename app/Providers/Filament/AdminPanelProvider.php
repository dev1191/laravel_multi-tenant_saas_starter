<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use App\Domain\Settings\Settings\PlatformBrandingSettings;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('central')
            ->brandName(function () {
                try {
                    return app(PlatformBrandingSettings::class)->brand_name ?: 'TenantForge Central';
                } catch (\Throwable) {
                    return 'TenantForge Central';
                }
            })
            ->brandLogo(function () {
                try {
                    $path = app(PlatformBrandingSettings::class)->logo_light_path;
                    if (! $path) {
                        return null;
                    }
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
                        return asset($path);
                    }
                    return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
                } catch (\Throwable) {
                    return null;
                }
            })
            ->darkModeBrandLogo(function () {
                try {
                    $path = app(PlatformBrandingSettings::class)->logo_dark_path;
                    if (! $path) {
                        return null;
                    }
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
                        return asset($path);
                    }
                    return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
                } catch (\Throwable) {
                    return null;
                }
            })
            ->favicon(function () {
                try {
                    $path = app(PlatformBrandingSettings::class)->favicon_path;
                    if (! $path) {
                        return null;
                    }
                    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
                        return asset($path);
                    }
                    return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
                } catch (\Throwable) {
                    return null;
                }
            })
            ->colors(fn () => [
                'primary' => (function () {
                    try {
                        return app(PlatformBrandingSettings::class)->primary_color ?: '#4f46e5';
                    } catch (\Throwable) {
                        return '#4f46e5';
                    }
                })(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

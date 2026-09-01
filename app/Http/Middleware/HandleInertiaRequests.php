<?php

namespace App\Http\Middleware;

use App\Domain\Settings\Settings\SiteSettings;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Pennant\Feature;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $tenant = tenant();
        $user = $request->user();

        $tenantData = null;
        $siteSettingsData = null;
        $features = [];

        if ($tenant) {
            $tenantData = [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'plan' => $tenant->plan,
                'status' => $tenant->status,
                'on_trial' => $tenant->onTrial(),
                'has_expired_trial' => $tenant->hasExpiredTrial(),
                'trial_ends_at' => $tenant->trial_ends_at?->toFormattedDateString(),
                'default_currency' => $tenant->default_currency ?? 'USD',
                'default_locale' => $tenant->default_locale ?? 'en',
                'timezone' => $tenant->timezone ?? 'UTC',
            ];

            try {
                $siteSettings = app(SiteSettings::class);
                $siteSettingsData = [
                    'site_name' => $siteSettings->site_name ?? $tenant->name,
                    'logo_path' => $siteSettings->logo_light_path ?? $siteSettings->logo_path ?? null,
                    'logo_light_path' => $siteSettings->logo_light_path ?? $siteSettings->logo_path ?? null,
                    'logo_dark_path' => $siteSettings->logo_dark_path ?? null,
                    'primary_color' => $siteSettings->primary_color ?? '#4f46e5',
                    'theme' => $siteSettings->theme ?? 'system',
                    'default_locale' => $siteSettings->default_locale ?? 'en',
                    'default_currency' => $siteSettings->default_currency ?? 'USD',
                    'timezone' => $siteSettings->timezone ?? 'UTC',
                    'registration_enabled' => $siteSettings->registration_enabled ?? true,
                ];
            } catch (\Throwable) {
                $siteSettingsData = null;
            }

            $features = [
                'team_invites' => Feature::active('team-invites'),
                'advanced_analytics' => Feature::active('advanced-analytics'),
                'custom_branding' => Feature::active('custom-branding'),
            ];
        }

        $currentTeam = null;
        $highestRoleLevel = 0;
        if ($user && $tenant) {
            $currentTeam = $user->currentTeam();
            $highestRoleLevel = $user->highestRoleLevel($currentTeam);
        }

        $unreadNotificationsCount = 0;
        $recentNotifications = [];
        if ($user) {
            try {
                $unreadNotificationsCount = $user->unreadNotifications()->count();
                $recentNotifications = $user->notifications()
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn ($n) => [
                        'id' => $n->id,
                        'type' => $n->data['type'] ?? 'info',
                        'title' => $n->data['title'] ?? 'Notification',
                        'message' => $n->data['message'] ?? '',
                        'action_url' => $n->data['action_url'] ?? null,
                        'action_text' => $n->data['action_text'] ?? null,
                        'read_at' => $n->read_at ? $n->read_at->toISOString() : null,
                        'created_at' => $n->created_at->diffForHumans(),
                    ]);
            } catch (\Throwable) {
                // Ignore if table not yet migrated
            }
        }

        return [
            ...parent::share($request),
            'name' => $siteSettingsData['site_name'] ?? config('app.name'),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar ?? null,
                    'locale' => $user->locale,
                    'timezone' => $user->timezone,
                    'date_format' => $user->date_format,
                    'time_format' => $user->time_format,
                    'role_level' => $highestRoleLevel,
                    'is_admin' => $highestRoleLevel >= 80,
                    'is_owner' => $highestRoleLevel >= 100,
                ] : null,
                'team' => $currentTeam ? [
                    'id' => $currentTeam->id,
                    'name' => $currentTeam->name,
                    'slug' => $currentTeam->slug,
                    'is_owner' => $currentTeam->owner_id === $user?->id,
                ] : null,
                'unread_notifications_count' => $unreadNotificationsCount,
                'notifications' => $recentNotifications,
            ],
            'tenant' => $tenantData,
            'site_settings' => $siteSettingsData,
            'features' => $features,
            'impersonation' => session('impersonation_token') ? [
                'active' => true,
                'staff_name' => session('impersonation_staff_name', 'Support Staff'),
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'warning' => fn () => $request->session()->get('warning'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'locale' => fn () => app()->getLocale(),
            'translations' => fn () => [
                'messages' => is_array(trans('messages')) ? trans('messages') : [],
            ],
            'currencies' => fn () => \App\Support\Currency::options(),
            'timezones' => fn () => \App\Support\Timezone::options(),
            'countries' => fn () => \App\Support\Country::options(),
            'locales' => fn () => \App\Support\Locale::options(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}

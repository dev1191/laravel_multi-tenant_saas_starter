<?php

namespace App\Domain\Settings\Controllers;

use App\Domain\Settings\Models\TenantLocale;
use App\Domain\Settings\Settings\SiteSettings;
use App\Domain\Teams\Models\Team;
use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Support\Currency;
use App\Support\Timezone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingsController extends Controller
{
    public function edit(Request $request, SiteSettings $settings): Response
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::first();

        if ($team && ! $user->hasRoleLevel(80, $team)) {
            abort(403, 'Unauthorized.');
        }

        $locales = TenantLocale::all();
        $availableLanguages = Language::orderBy('display_order')->get(['code', 'name', 'native_name', 'direction', 'flag']);

        return Inertia::render('settings/Site', [
            'settings' => [
                'site_name' => $settings->site_name,
                'logo_path' => $settings->logo_path ?? $settings->logo_light_path,
                'logo_light_path' => $settings->logo_light_path ?? $settings->logo_path,
                'logo_dark_path' => $settings->logo_dark_path,
                'primary_color' => $settings->primary_color,
                'theme' => $settings->theme ?? 'system',
                'default_locale' => $settings->default_locale,
                'default_currency' => $settings->default_currency,
                'timezone' => $settings->timezone,
                'registration_enabled' => $settings->registration_enabled,
                'mail_driver' => $settings->mail_driver ?? 'default',
                'mail_host' => $settings->mail_host ?? '',
                'mail_port' => $settings->mail_port ?? 587,
                'mail_username' => $settings->mail_username ?? '',
                'mail_password' => $settings->mail_password ? '********' : '',
                'mail_encryption' => $settings->mail_encryption ?? 'tls',
                'mail_from_address' => $settings->mail_from_address ?? '',
                'mail_from_name' => $settings->mail_from_name ?? '',
            ],
            'locales' => $locales,
            'available_languages' => $availableLanguages,
            'currencies' => Currency::options(),
            'timezones' => Timezone::options(),
        ]);
    }

    public function update(Request $request, SiteSettings $settings): RedirectResponse
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::first();

        if ($team && ! $user->hasRoleLevel(80, $team)) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'logo_light_path' => ['nullable', 'string', 'max:2048'],
            'logo_dark_path' => ['nullable', 'string', 'max:2048'],
            'primary_color' => ['required', 'string', 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'],
            'theme' => ['required', 'string', 'in:light,dark,system'],
            'default_locale' => ['required', 'string', 'max:5'],
            'default_currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'string', 'timezone'],
            'registration_enabled' => ['required', 'boolean'],
            'mail_driver' => ['required', 'string', 'in:default,smtp'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,none'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
        ]);

        $settings->site_name = $validated['site_name'];
        $settings->logo_light_path = $validated['logo_light_path'] ?? null;
        $settings->logo_dark_path = $validated['logo_dark_path'] ?? null;
        $settings->logo_path = $validated['logo_light_path'] ?? null; // backward compatibility
        $settings->primary_color = $validated['primary_color'];
        $settings->theme = $validated['theme'];
        $settings->default_locale = $validated['default_locale'];
        $settings->default_currency = strtoupper($validated['default_currency']);
        $settings->timezone = $validated['timezone'];
        $settings->registration_enabled = $validated['registration_enabled'];

        $settings->mail_driver = $validated['mail_driver'];
        $settings->mail_host = $validated['mail_host'];
        $settings->mail_port = $validated['mail_port'];
        $settings->mail_username = $validated['mail_username'];

        // Only update password if a new one was entered (not the masked placeholder)
        if (! empty($validated['mail_password']) && $validated['mail_password'] !== '********') {
            $settings->mail_password = $validated['mail_password'];
        }

        $settings->mail_encryption = $validated['mail_encryption'];
        $settings->mail_from_address = $validated['mail_from_address'];
        $settings->mail_from_name = $validated['mail_from_name'];

        $settings->save();

        return back()->with('success', 'Site settings updated successfully.');
    }

    public function testEmail(Request $request, SiteSettings $settings): RedirectResponse
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::first();

        if ($team && ! $user->hasRoleLevel(80, $team)) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'recipient_email' => ['required', 'email'],
        ]);

        try {
            Mail::raw("Hello from {$settings->site_name}! This is a test email confirming that your workspace mail configuration is operating properly.", function ($message) use ($validated, $settings) {
                $message->to($validated['recipient_email'])
                    ->subject("Test Email from {$settings->site_name}");
            });

            return back()->with('success', "Test email successfully sent to {$validated['recipient_email']}!");
        } catch (\Throwable $e) {
            return back()->with('error', "Failed to send test email: {$e->getMessage()}");
        }
    }

    public function emailPreview(Request $request, SiteSettings $settings): \Illuminate\Http\Response
    {
        $user = $request->user();
        $team = $user->currentTeam() ?? Team::first();

        if ($team && ! $user->hasRoleLevel(80, $team)) {
            abort(403, 'Unauthorized.');
        }

        $brandName = $request->query('site_name', $settings->site_name);
        $primaryColor = $request->query('primary_color', $settings->primary_color ?? '#4f46e5');
        $logoUrl = $settings->logo_path ? asset($settings->logo_path) : null;

        $html = view('emails.team_invitation', [
            'teamName' => $brandName,
            'inviterName' => $user->name ?? 'A team admin',
            'role' => 'Member',
            'inviteUrl' => '#',
            'expiresAt' => now()->addDays(7)->toFormattedDateString(),
            'primaryColor' => $primaryColor,
            'logoUrl' => $logoUrl,
        ])->render();

        return response($html)->header('Content-Type', 'text/html');
    }

    public function storeLocale(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:5', 'unique:tenant_locales,code'],
            'name' => ['required', 'string', 'max:100'],
            'direction' => ['required', 'in:ltr,rtl'],
            'is_default' => ['boolean'],
        ]);

        TenantLocale::create($validated);

        return back()->with('success', 'New supported locale added.');
    }
}


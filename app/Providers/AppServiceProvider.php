<?php

namespace App\Providers;

use App\Domain\Settings\Settings\PlatformMailSettings;
use App\Models\Tenant;
use App\Support\Locale;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('Support/helpers.php'))) {
            require_once app_path('Support/helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->bootstrapPlatformMail();

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(fn () => Locale::activeCodes())
                ->visible(outsidePanels: false);
        });

        Cashier::useCustomerModel(Tenant::class);
        Cashier::calculateTaxes();

        \Illuminate\Support\Facades\Event::subscribe(\App\Listeners\AuthEventSubscriber::class);

        \Filament\Actions\Exports\Models\Export::polymorphicUserRelationship();
        \Filament\Actions\Imports\Models\Import::polymorphicUserRelationship();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Apply platform-level mail settings from DB to Laravel config.
     * Skipped during migrations / fresh installs when the settings table may not exist yet.
     */
    protected function bootstrapPlatformMail(): void
    {
        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('settings')) {
                return;
            }

            $settings = app(PlatformMailSettings::class);
        } catch (\Throwable) {
            return;
        }

        if ($settings->mailer === 'smtp') {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $settings->host,
                'mail.mailers.smtp.port' => $settings->port ?? 587,
                'mail.mailers.smtp.username' => $settings->username,
                'mail.mailers.smtp.password' => $settings->password,
                'mail.mailers.smtp.encryption' => $settings->encryption === 'none' ? null : $settings->encryption,
            ]);
        } else {
            config(['mail.default' => $settings->mailer]);
        }

        if ($settings->from_address) {
            config(['mail.from.address' => $settings->from_address]);
        }
        if ($settings->from_name) {
            config(['mail.from.name' => $settings->from_name]);
        }
    }
}

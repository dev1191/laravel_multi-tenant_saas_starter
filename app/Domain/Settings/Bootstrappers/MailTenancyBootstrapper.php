<?php

declare(strict_types=1);

namespace App\Domain\Settings\Bootstrappers;

use App\Domain\Settings\Settings\SiteSettings;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Mail\MailManager;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class MailTenancyBootstrapper implements TenancyBootstrapper
{
    protected ?array $originalMailConfig = null;

    public function __construct(
        protected Application $app,
        protected MailManager $mailManager,
    ) {
    }

    public function bootstrap(Tenant $tenant): void
    {
        $this->originalMailConfig = [
            'default' => config('mail.default'),
            'from' => config('mail.from'),
            'tenant_smtp' => config('mail.mailers.tenant_smtp'),
        ];

        try {
            $settings = $this->app->make(SiteSettings::class);

            if ($settings->mail_driver === 'smtp' && ! empty($settings->mail_host)) {
                config([
                    'mail.mailers.tenant_smtp' => [
                        'transport' => 'smtp',
                        'host' => $settings->mail_host,
                        'port' => $settings->mail_port ?? 587,
                        'encryption' => $settings->mail_encryption === 'none' ? null : ($settings->mail_encryption ?? 'tls'),
                        'username' => $settings->mail_username,
                        'password' => $settings->mail_password,
                        'timeout' => null,
                        'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
                    ],
                    'mail.default' => 'tenant_smtp',
                ]);

                if (! empty($settings->mail_from_address)) {
                    config(['mail.from.address' => $settings->mail_from_address]);
                }

                if (! empty($settings->mail_from_name)) {
                    config(['mail.from.name' => $settings->mail_from_name]);
                } elseif (! empty($settings->site_name)) {
                    config(['mail.from.name' => $settings->site_name]);
                }

                $this->mailManager->forgetMailers();
            } elseif (! empty($settings->mail_from_name) || ! empty($settings->site_name)) {
                config([
                    'mail.from.name' => $settings->mail_from_name ?: $settings->site_name,
                ]);

                if (! empty($settings->mail_from_address)) {
                    config(['mail.from.address' => $settings->mail_from_address]);
                }

                $this->mailManager->forgetMailers();
            }
        } catch (\Throwable) {
            // Silently fall back to platform default if tenant settings table or record is uninitialized
        }
    }

    public function revert(): void
    {
        if ($this->originalMailConfig !== null) {
            config([
                'mail.default' => $this->originalMailConfig['default'],
                'mail.from' => $this->originalMailConfig['from'],
                'mail.mailers.tenant_smtp' => $this->originalMailConfig['tenant_smtp'],
            ]);

            $this->mailManager->forgetMailers();
            $this->originalMailConfig = null;
        }
    }
}

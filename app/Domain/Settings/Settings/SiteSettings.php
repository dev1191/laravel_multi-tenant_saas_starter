<?php

namespace App\Domain\Settings\Settings;

use Spatie\LaravelSettings\Settings;

class SiteSettings extends Settings
{
    public string $site_name;

    public ?string $logo_path;

    public ?string $logo_light_path;

    public ?string $logo_dark_path;

    public string $primary_color;

    public string $theme;

    public string $default_locale;

    public string $default_currency;

    public string $timezone;

    public bool $registration_enabled;

    public string $mail_driver;

    public ?string $mail_host;

    public ?int $mail_port;

    public ?string $mail_username;

    public ?string $mail_password;

    public ?string $mail_encryption;

    public ?string $mail_from_address;

    public ?string $mail_from_name;

    public string $storage_driver;

    public ?string $storage_key;

    public ?string $storage_secret;

    public ?string $storage_bucket;

    public ?string $storage_region;

    public ?string $storage_endpoint;

    public bool $storage_use_path_style_endpoint;

    public static function group(): string
    {
        return 'site';
    }
}

<?php

namespace App\Domain\Settings\Settings;

use Spatie\LaravelSettings\Settings;

class PlatformBrandingSettings extends Settings
{
    public string $brand_name;

    public ?string $logo_light_path;

    public ?string $logo_dark_path;

    public ?string $favicon_path;

    public string $primary_color;

    public static function group(): string
    {
        return 'platform_branding';
    }
}

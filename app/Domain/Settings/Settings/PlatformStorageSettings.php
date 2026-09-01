<?php

namespace App\Domain\Settings\Settings;

use Spatie\LaravelSettings\Settings;

class PlatformStorageSettings extends Settings
{
    public string $driver;

    public ?string $key;

    public ?string $secret;

    public ?string $bucket;

    public ?string $region;

    public ?string $endpoint;

    public bool $use_path_style_endpoint;

    public static function group(): string
    {
        return 'platform_storage';
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Str;
use Symfony\Component\Intl\Countries;

class Country
{
    /**
     * Get all country names keyed by ISO 3166-1 alpha-2 code.
     *
     * @return array<string, string>
     */
    public static function all(?string $displayLocale = null): array
    {
        return Countries::getNames($displayLocale);
    }

    /**
     * Get options formatted for select inputs/dropdowns.
     *
     * @return array<string, string>
     */
    public static function options(?string $displayLocale = null): array
    {
        return static::all($displayLocale);
    }

    /**
     * Get the country name for a given ISO code.
     */
    public static function getName(string $code, ?string $displayLocale = null): ?string
    {
        $code = Str::upper(trim($code));

        return Countries::exists($code) ? Countries::getName($code, $displayLocale) : null;
    }

    /**
     * Check if a country code exists.
     */
    public static function exists(string $code): bool
    {
        return Countries::exists(Str::upper(trim($code)));
    }
}

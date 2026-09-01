<?php

namespace App\Support;

use Symfony\Component\Intl\Timezones;

class Timezone
{
    /**
     * Get all timezone names keyed by timezone identifier.
     * e.g. ['America/New_York' => 'Eastern Time (New York)', ...]
     *
     * @return array<string, string>
     */
    public static function all(?string $displayLocale = null): array
    {
        return Timezones::getNames($displayLocale);
    }

    /**
     * Get timezone options formatted for select dropdowns.
     * e.g. ['America/New_York' => '(GMT-05:00) Eastern Time (New York) [America/New_York]', ...]
     *
     * @return array<string, string>
     */
    public static function options(?string $displayLocale = null): array
    {
        $options = [];
        $names = static::all($displayLocale);

        foreach ($names as $timezone => $name) {
            $offset = static::getGmtOffset($timezone);
            $options[$timezone] = $offset
                ? "({$offset}) {$name} [{$timezone}]"
                : "{$name} [{$timezone}]";
        }

        return $options;
    }

    /**
     * Get the human-readable timezone name for a given identifier.
     */
    public static function getName(string $timezone, ?string $displayLocale = null): ?string
    {
        return Timezones::exists($timezone) ? Timezones::getName($timezone, $displayLocale) : null;
    }

    /**
     * Get the GMT/UTC offset string (e.g. 'GMT+05:45', 'GMT-05:00').
     */
    public static function getGmtOffset(string $timezone, ?int $timestamp = null, ?string $displayLocale = null): ?string
    {
        return Timezones::exists($timezone) ? Timezones::getGmtOffset($timezone, $timestamp, $displayLocale) : null;
    }

    /**
     * Check if a timezone identifier exists.
     */
    public static function exists(string $timezone): bool
    {
        return Timezones::exists($timezone);
    }
}

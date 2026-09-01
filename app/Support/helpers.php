<?php

declare(strict_types=1);

use App\Support\Locale;

if (! function_exists('current_locale')) {
    /**
     * Get the active application locale.
     */
    function current_locale(): string
    {
        return Locale::current();
    }
}

if (! function_exists('is_rtl')) {
    /**
     * Check if the current or specified locale is Right-to-Left.
     */
    function is_rtl(?string $locale = null): bool
    {
        return Locale::isRtl($locale);
    }
}

if (! function_exists('is_ltr')) {
    /**
     * Check if the current or specified locale is Left-to-Right.
     */
    function is_ltr(?string $locale = null): bool
    {
        return Locale::isLtr($locale);
    }
}

if (! function_exists('locale_direction')) {
    /**
     * Get the text direction ('ltr' or 'rtl') for a locale.
     */
    function locale_direction(?string $locale = null): string
    {
        return Locale::direction($locale);
    }
}

if (! function_exists('locale_flag')) {
    /**
     * Get the flag emoji for a locale.
     */
    function locale_flag(string $locale): string
    {
        return Locale::getFlag($locale);
    }
}

if (! function_exists('active_locales')) {
    /**
     * Get active dynamic locales array.
     *
     * @return array<string>
     */
    function active_locales(): array
    {
        return Locale::activeCodes();
    }
}

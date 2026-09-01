<?php

declare(strict_types=1);

namespace App\Support;

use App\Domain\TenantAdmin\Models\Language;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\Locales;

class Locale
{
    /**
     * Known RTL (right-to-left) language codes.
     */
    protected const RTL_LOCALES = [
        'ar', 'fa', 'he', 'ur', 'ps', 'sd', 'ug', 'yi', 'dv', 'ckb', 'arc', 'syr',
    ];

    /**
     * Common flag emoji mapping.
     */
    protected const DEFAULT_FLAGS = [
        'en' => '🇬🇧',
        'es' => '🇪🇸',
        'fr' => '🇫🇷',
        'de' => '🇩🇪',
        'pt_BR' => '🇧🇷',
        'pt' => '🇵🇹',
        'ar' => '🇸🇦',
        'zh_CN' => '🇨🇳',
        'zh' => '🇨🇳',
        'ja' => '🇯🇵',
        'ne' => '🇳🇵',
        'hi' => '🇮🇳',
        'it' => '🇮🇹',
        'ru' => '🇷🇺',
        'ko' => '🇰🇷',
        'nl' => '🇳🇱',
        'tr' => '🇹🇷',
        'ur' => '🇵🇰',
        'fa' => '🇮🇷',
        'he' => '🇮🇱',
    ];

    /**
     * Get the current active application locale.
     */
    public static function current(): string
    {
        return app()->getLocale();
    }

    /**
     * Determine if a locale (or current locale) is Right-to-Left (RTL).
     */
    public static function isRtl(?string $locale = null): bool
    {
        $locale = $locale ?? static::current();
        $base = strtolower(explode('_', str_replace('-', '_', $locale))[0]);

        return in_array($base, self::RTL_LOCALES, true);
    }

    /**
     * Determine if a locale (or current locale) is Left-to-Right (LTR).
     */
    public static function isLtr(?string $locale = null): bool
    {
        return ! static::isRtl($locale);
    }

    /**
     * Get text direction ('ltr' or 'rtl') for a given locale.
     */
    public static function direction(?string $locale = null): string
    {
        return static::isRtl($locale) ? 'rtl' : 'ltr';
    }

    /**
     * Get all available standard languages from Symfony Intl.
     *
     * @return array<string, string>
     */
    public static function all(?string $displayLocale = null): array
    {
        return Languages::getNames($displayLocale);
    }

    /**
     * Get locale options formatted for select dropdowns.
     *
     * @return array<string, string>
     */
    public static function options(?string $displayLocale = null): array
    {
        $options = [];
        foreach (static::all($displayLocale) as $code => $name) {
            $options[$code] = "{$name} ({$code})";
        }

        return $options;
    }

    /**
     * Get the localized language name for a given locale code.
     */
    public static function getName(string $code, ?string $displayLocale = null): ?string
    {
        $normalized = str_replace('_', '-', $code);

        if (Languages::exists($code)) {
            return Languages::getName($code, $displayLocale);
        }

        if (Languages::exists($normalized)) {
            return Languages::getName($normalized, $displayLocale);
        }

        return Locales::exists($code) ? Locales::getName($code, $displayLocale) : $code;
    }

    /**
     * Get the native language name (in its own script/language).
     */
    public static function getNativeName(string $code): ?string
    {
        return static::getName($code, $code);
    }

    /**
     * Get flag emoji for a given locale code.
     */
    public static function getFlag(string $code): string
    {
        return self::DEFAULT_FLAGS[$code]
            ?? self::DEFAULT_FLAGS[strtolower(explode('_', str_replace('-', '_', $code))[0])]
            ?? '🌐';
    }

    /**
     * Check if a locale code is valid in Symfony Intl.
     */
    public static function exists(string $code): bool
    {
        $normalized = str_replace('_', '-', $code);

        return Languages::exists($code) || Languages::exists($normalized) || Locales::exists($code);
    }

    /**
     * Get list of active dynamic language codes.
     *
     * @return array<string>
     */
    public static function activeCodes(): array
    {
        try {
            if (Schema::hasTable('languages')) {
                $codes = Language::query()
                    ->active()
                    ->orderBy('display_order')
                    ->pluck('code')
                    ->all();

                if (! empty($codes)) {
                    return $codes;
                }
            }
        } catch (\Throwable) {
            // Fallback during initial boot
        }

        return ['en', 'es', 'fr', 'de', 'pt_BR', 'ar', 'zh_CN', 'ja', 'ne'];
    }

    /**
     * Get active dynamic languages formatted as key-value pairs for switchers/dropdowns.
     *
     * @return array<string, string>
     */
    public static function activeOptions(): array
    {
        try {
            if (Schema::hasTable('languages')) {
                $languages = Language::query()
                    ->active()
                    ->orderBy('display_order')
                    ->get();

                if ($languages->isNotEmpty()) {
                    return $languages->mapWithKeys(fn (Language $lang) => [
                        $lang->code => ($lang->flag ? "{$lang->flag} " : '').($lang->native_name ?: $lang->name),
                    ])->all();
                }
            }
        } catch (\Throwable) {
            // Fallback
        }

        $defaults = ['en', 'es', 'fr', 'de', 'pt_BR', 'ar', 'zh_CN', 'ja', 'ne'];
        $options = [];
        foreach ($defaults as $code) {
            $options[$code] = static::getFlag($code).' '.(static::getNativeName($code) ?: static::getName($code));
        }

        return $options;
    }
}

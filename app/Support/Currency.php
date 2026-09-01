<?php

namespace App\Support;

use Illuminate\Support\Str;
use NumberFormatter;
use Symfony\Component\Intl\Currencies;

class Currency
{
    /**
     * Get all currency names keyed by ISO 4217 code.
     *
     * @return array<string, string>
     */
    public static function all(?string $displayLocale = null): array
    {
        return Currencies::getNames($displayLocale);
    }

    /**
     * Get currency options formatted with symbol for select dropdowns.
     * e.g. ['USD' => 'USD ($) - US Dollar', 'EUR' => 'EUR (€) - Euro', ...]
     *
     * @return array<string, string>
     */
    public static function options(?string $displayLocale = null): array
    {
        $options = [];
        foreach (static::all($displayLocale) as $code => $name) {
            $symbol = static::getSymbol($code, $displayLocale);
            $options[$code] = $symbol && $symbol !== $code
                ? "{$code} ({$symbol}) - {$name}"
                : "{$code} - {$name}";
        }

        return $options;
    }

    /**
     * Get the currency name for a given ISO code.
     */
    public static function getName(string $code, ?string $displayLocale = null): ?string
    {
        $code = Str::upper(trim($code));

        return Currencies::exists($code) ? Currencies::getName($code, $displayLocale) : null;
    }

    /**
     * Get the currency symbol (e.g. '$', '€', '£') for a given ISO code.
     */
    public static function getSymbol(string $code, ?string $displayLocale = null): ?string
    {
        $code = Str::upper(trim($code));

        return Currencies::exists($code) ? Currencies::getSymbol($code, $displayLocale) : null;
    }

    /**
     * Get fraction digits (decimal places) for a currency.
     */
    public static function getFractionDigits(string $code): int
    {
        $code = Str::upper(trim($code));

        return Currencies::exists($code) ? Currencies::getFractionDigits($code) : 2;
    }

    /**
     * Check if a currency code exists.
     */
    public static function exists(string $code): bool
    {
        return Currencies::exists(Str::upper(trim($code)));
    }

    /**
     * Format a monetary amount for a given currency code.
     */
    public static function format(float|int $amount, string $currency = 'USD', string $locale = 'en'): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($amount, Str::upper($currency)) ?: number_format($amount, 2).' '.$currency;
    }
}

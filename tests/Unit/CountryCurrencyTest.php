<?php

use App\Support\Country;
use App\Support\Currency;

test('it resolves country names and existence', function () {
    expect(Country::getName('US'))->toBe('United States')
        ->and(Country::getName('GB'))->toBe('United Kingdom')
        ->and(Country::exists('US'))->toBeTrue()
        ->and(Country::exists('INVALID'))->toBeFalse();

    $options = Country::options();
    expect($options)->toHaveKey('US')
        ->and($options['US'])->toBe('United States');
});

test('it resolves currency symbols and formats amount', function () {
    expect(Currency::getName('USD'))->toBe('US Dollar')
        ->and(Currency::getSymbol('USD'))->toBe('$')
        ->and(Currency::getSymbol('EUR'))->toBe('€')
        ->and(Currency::exists('USD'))->toBeTrue()
        ->and(Currency::exists('XYZ123'))->toBeFalse();

    $formatted = Currency::format(29.99, 'USD');
    expect($formatted)->toContain('$29.99');
});

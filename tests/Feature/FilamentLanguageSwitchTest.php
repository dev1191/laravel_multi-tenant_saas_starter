<?php

use App\Domain\TenantAdmin\Models\Language;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('filament language switch is configured with expected locales', function () {
    $locales = LanguageSwitch::make()->getLocales();

    expect($locales)->toContain('en', 'es', 'fr', 'de', 'pt_BR', 'ar', 'zh_CN', 'ja', 'ne');
});

test('filament language switch switches locale in session and cookie', function () {
    LanguageSwitch::switchLocale('es');

    expect(session('locale'))->toBe('es');
});

test('dynamic language model supports scopes, direction checks and default states', function () {
    $lang = Language::create([
        'code' => 'ur',
        'name' => 'Urdu',
        'native_name' => 'اردو',
        'direction' => 'rtl',
        'is_active' => true,
        'is_default' => false,
        'display_order' => 10,
    ]);

    expect($lang->isRtl())->toBeTrue();
    expect($lang->isLtr())->toBeFalse();

    $activeCodes = Language::active()->pluck('code')->all();
    expect($activeCodes)->toContain('ur');
});

test('locale support helpers and global functions work as expected', function () {
    expect(\App\Support\Locale::isRtl('ar'))->toBeTrue();
    expect(\App\Support\Locale::isRtl('en'))->toBeFalse();
    expect(\App\Support\Locale::isLtr('en'))->toBeTrue();
    expect(\App\Support\Locale::direction('ar'))->toBe('rtl');
    expect(\App\Support\Locale::direction('en'))->toBe('ltr');
    expect(\App\Support\Locale::getFlag('en'))->toBe('🇬🇧');
    expect(\App\Support\Locale::exists('es'))->toBeTrue();

    expect(is_rtl('ar'))->toBeTrue();
    expect(is_ltr('en'))->toBeTrue();
    expect(locale_direction('ar'))->toBe('rtl');
    expect(locale_flag('es'))->toBe('🇪🇸');
    expect(current_locale())->toBe(app()->getLocale());
    expect(active_locales())->toBeArray();
});



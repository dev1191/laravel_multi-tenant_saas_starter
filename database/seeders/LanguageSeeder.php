<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\TenantAdmin\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'native_name' => 'English',
                'direction' => 'ltr',
                'flag' => '🇬🇧',
                'is_active' => true,
                'is_default' => true,
                'display_order' => 1,
            ],
            [
                'code' => 'es',
                'name' => 'Spanish',
                'native_name' => 'Español',
                'direction' => 'ltr',
                'flag' => '🇪🇸',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 2,
            ],
            [
                'code' => 'fr',
                'name' => 'French',
                'native_name' => 'Français',
                'direction' => 'ltr',
                'flag' => '🇫🇷',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 3,
            ],
            [
                'code' => 'de',
                'name' => 'German',
                'native_name' => 'Deutsch',
                'direction' => 'ltr',
                'flag' => '🇩🇪',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 4,
            ],
            [
                'code' => 'pt_BR',
                'name' => 'Portuguese (Brazil)',
                'native_name' => 'Português (Brasil)',
                'direction' => 'ltr',
                'flag' => '🇧🇷',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 5,
            ],
            [
                'code' => 'ar',
                'name' => 'Arabic',
                'native_name' => 'العربية',
                'direction' => 'rtl',
                'flag' => '🇸🇦',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 6,
            ],
            [
                'code' => 'zh_CN',
                'name' => 'Chinese (Simplified)',
                'native_name' => '简体中文',
                'direction' => 'ltr',
                'flag' => '🇨🇳',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 7,
            ],
            [
                'code' => 'ja',
                'name' => 'Japanese',
                'native_name' => '日本語',
                'direction' => 'ltr',
                'flag' => '🇯🇵',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 8,
            ],
            [
                'code' => 'ne',
                'name' => 'Nepali',
                'native_name' => 'नेपाली',
                'direction' => 'ltr',
                'flag' => '🇳🇵',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 9,
            ],
            [
                'code' => 'hi',
                'name' => 'Hindi',
                'native_name' => 'हिन्दी',
                'direction' => 'ltr',
                'flag' => '🇮🇳',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 10,
            ],
            [
                'code' => 'it',
                'name' => 'Italian',
                'native_name' => 'Italiano',
                'direction' => 'ltr',
                'flag' => '🇮🇹',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 11,
            ],
            [
                'code' => 'ru',
                'name' => 'Russian',
                'native_name' => 'Русский',
                'direction' => 'ltr',
                'flag' => '🇷🇺',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 12,
            ],
            [
                'code' => 'nl',
                'name' => 'Dutch',
                'native_name' => 'Nederlands',
                'direction' => 'ltr',
                'flag' => '🇳🇱',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 13,
            ],
            [
                'code' => 'tr',
                'name' => 'Turkish',
                'native_name' => 'Türkçe',
                'direction' => 'ltr',
                'flag' => '🇹🇷',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 14,
            ],
            [
                'code' => 'ur',
                'name' => 'Urdu',
                'native_name' => 'اردو',
                'direction' => 'rtl',
                'flag' => '🇵🇰',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 15,
            ],
            [
                'code' => 'fa',
                'name' => 'Persian',
                'native_name' => 'فارسی',
                'direction' => 'rtl',
                'flag' => '🇮🇷',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 16,
            ],
            [
                'code' => 'he',
                'name' => 'Hebrew',
                'native_name' => 'עברית',
                'direction' => 'rtl',
                'flag' => '🇮🇱',
                'is_active' => true,
                'is_default' => false,
                'display_order' => 17,
            ],
        ];

        foreach ($languages as $data) {
            Language::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('native_name')->nullable();
            $table->string('direction', 3)->default('ltr'); // 'ltr' | 'rtl'
            $table->string('flag', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });

        // Seed default supported languages
        $defaultLanguages = [
            ['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'direction' => 'ltr', 'flag' => '🇬🇧', 'is_active' => true, 'is_default' => true, 'display_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'es', 'name' => 'Spanish', 'native_name' => 'Español', 'direction' => 'ltr', 'flag' => '🇪🇸', 'is_active' => true, 'is_default' => false, 'display_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'fr', 'name' => 'French', 'native_name' => 'Français', 'direction' => 'ltr', 'flag' => '🇫🇷', 'is_active' => true, 'is_default' => false, 'display_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'de', 'name' => 'German', 'native_name' => 'Deutsch', 'direction' => 'ltr', 'flag' => '🇩🇪', 'is_active' => true, 'is_default' => false, 'display_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'pt_BR', 'name' => 'Portuguese (Brazil)', 'native_name' => 'Português (Brasil)', 'direction' => 'ltr', 'flag' => '🇧🇷', 'is_active' => true, 'is_default' => false, 'display_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ar', 'name' => 'Arabic', 'native_name' => 'العربية', 'direction' => 'rtl', 'flag' => '🇸🇦', 'is_active' => true, 'is_default' => false, 'display_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'zh_CN', 'name' => 'Chinese (Simplified)', 'native_name' => '简体中文', 'direction' => 'ltr', 'flag' => '🇨🇳', 'is_active' => true, 'is_default' => false, 'display_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'direction' => 'ltr', 'flag' => '🇯🇵', 'is_active' => true, 'is_default' => false, 'display_order' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'ne', 'name' => 'Nepali', 'native_name' => 'नेपाली', 'direction' => 'ltr', 'flag' => '🇳🇵', 'is_active' => true, 'is_default' => false, 'display_order' => 9, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('languages')->insert($defaultLanguages);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};

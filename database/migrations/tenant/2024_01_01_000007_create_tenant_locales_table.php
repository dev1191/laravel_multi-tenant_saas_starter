<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenant_locales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique();   // 'en', 'ar', 'pt-BR'
            $table->string('name');                // 'English', 'العربية'
            $table->string('direction', 3)->default('ltr'); // 'ltr' | 'rtl'
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_locales');
    }
};

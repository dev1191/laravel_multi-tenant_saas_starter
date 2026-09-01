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
        Schema::create('plan_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('currency', 3)->default('USD'); // ISO 4217
            $table->integer('amount'); // in smallest unit (e.g. cents/cents/paise)
            $table->string('gateway')->default('stripe'); // 'stripe' | 'paddle' | etc.
            $table->string('gateway_price_id')->nullable(); // the gateway's price/plan ID
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_prices');
    }
};

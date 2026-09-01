<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();

            // TenantForge central columns
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('plan')->default('trial');   // ties into billing, per our central-DB rule
            $table->string('status')->default('active'); // active/suspended/trial/provisioning
            $table->timestamp('trial_ends_at')->nullable();
            $table->string('country_code', 2)->nullable(); // ISO 3166-1 alpha-2
            $table->string('default_currency', 3)->default('USD'); // ISO 4217
            $table->string('default_locale', 5)->default('en');
            $table->string('timezone')->default('UTC');
            $table->string('preferred_gateway')->nullable();
            $table->string('tax_id')->nullable();
            $table->boolean('tax_exempt')->default(false);
            $table->json('billing_address')->nullable();
            $table->string('db_host')->nullable(); // overrides default DB host for GDPR data residency routing
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}

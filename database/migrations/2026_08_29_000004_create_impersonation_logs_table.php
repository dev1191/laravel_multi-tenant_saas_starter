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
        Schema::create('impersonation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique(); // token bridging central log to tenant-side activity log
            $table->foreignId('central_user_id')->constrained('central_users')->cascadeOnDelete();
            $table->string('tenant_id');
            $table->unsignedBigInteger('impersonated_user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('impersonation_logs');
    }
};

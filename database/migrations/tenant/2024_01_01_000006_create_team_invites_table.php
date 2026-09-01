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
        Schema::create('team_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('email');
            $table->string('role')->default('member'); // role to assign on acceptance
            $table->string('token', 64)->unique();     // signed, random token
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending | accepted | expired | revoked
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_invites');
    }
};

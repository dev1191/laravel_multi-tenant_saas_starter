<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('settings.repositories.database.table') ?? 'settings', function (Blueprint $table): void {
            $table->id();

            $table->string('group');
            $table->string('name');
            $table->boolean('locked')->default(false);
            $table->json('payload');

            $table->timestamps();

            $table->unique(['group', 'name']);
        });

        $defaults = [
            'site_name' => 'Workspace',
            'logo_path' => null,
            'primary_color' => '#4f46e5',
            'theme' => 'system',
            'default_locale' => 'en',
            'default_currency' => 'USD',
            'timezone' => 'UTC',
            'registration_enabled' => true,
            'mail_driver' => 'default',
            'mail_host' => null,
            'mail_port' => 587,
            'mail_username' => null,
            'mail_password' => null,
            'mail_encryption' => 'tls',
            'mail_from_address' => null,
            'mail_from_name' => null,
        ];

        foreach ($defaults as $name => $value) {
            DB::table('settings')->insert([
                'group' => 'site',
                'name' => $name,
                'locked' => false,
                'payload' => json_encode($value),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};

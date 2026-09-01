<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'mailer' => env('MAIL_MAILER', 'log'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => (int) env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@tenantforge.com'),
            'from_name' => env('MAIL_FROM_NAME', config('app.name', 'TenantForge')),
        ];

        foreach ($defaults as $name => $value) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'platform_mail', 'name' => $name],
                [
                    'locked' => false,
                    'payload' => json_encode($value),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('group', 'platform_mail')->delete();
    }
};

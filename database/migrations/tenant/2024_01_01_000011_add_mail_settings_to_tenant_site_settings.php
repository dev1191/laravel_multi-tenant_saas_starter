<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
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
            $exists = DB::table('settings')->where('group', 'site')->where('name', $name)->exists();
            if (! $exists) {
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
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'site')
            ->whereIn('name', [
                'mail_driver',
                'mail_host',
                'mail_port',
                'mail_username',
                'mail_password',
                'mail_encryption',
                'mail_from_address',
                'mail_from_name',
            ])
            ->delete();
    }
};

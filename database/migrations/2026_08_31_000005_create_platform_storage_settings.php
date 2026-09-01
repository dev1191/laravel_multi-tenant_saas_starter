<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'driver' => env('FILESYSTEM_DISK', 'local'),
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'bucket' => env('AWS_BUCKET'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => (bool) env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ];

        foreach ($defaults as $name => $value) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'platform_storage', 'name' => $name],
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
        DB::table('settings')->where('group', 'platform_storage')->delete();
    }
};

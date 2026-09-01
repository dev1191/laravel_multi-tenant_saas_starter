<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'storage_driver' => 'default',
            'storage_key' => null,
            'storage_secret' => null,
            'storage_bucket' => null,
            'storage_region' => 'us-east-1',
            'storage_endpoint' => null,
            'storage_use_path_style_endpoint' => false,
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
                'storage_driver',
                'storage_key',
                'storage_secret',
                'storage_bucket',
                'storage_region',
                'storage_endpoint',
                'storage_use_path_style_endpoint',
            ])
            ->delete();
    }
};

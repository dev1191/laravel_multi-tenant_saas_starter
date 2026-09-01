<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaults = [
            'brand_name' => config('app.name', 'TenantForge'),
            'logo_light_path' => null,
            'logo_dark_path' => null,
            'favicon_path' => null,
            'primary_color' => '#4f46e5',
        ];

        foreach ($defaults as $name => $value) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'platform_branding', 'name' => $name],
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
        DB::table('settings')->where('group', 'platform_branding')->delete();
    }
};

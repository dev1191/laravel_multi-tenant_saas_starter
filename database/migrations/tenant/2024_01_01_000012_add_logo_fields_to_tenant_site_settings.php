<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $existingLogo = DB::table('settings')
            ->where('group', 'site')
            ->where('name', 'logo_path')
            ->value('payload');

        $defaults = [
            'logo_light_path' => $existingLogo ? json_decode($existingLogo) : null,
            'logo_dark_path' => null,
        ];

        foreach ($defaults as $name => $value) {
            DB::table('settings')->updateOrInsert(
                ['group' => 'site', 'name' => $name],
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
        DB::table('settings')
            ->where('group', 'site')
            ->whereIn('name', ['logo_light_path', 'logo_dark_path'])
            ->delete();
    }
};

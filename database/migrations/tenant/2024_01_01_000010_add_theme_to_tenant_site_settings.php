<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('settings')->where('group', 'site')->where('name', 'theme')->exists();
        if (! $exists) {
            DB::table('settings')->insert([
                'group' => 'site',
                'name' => 'theme',
                'locked' => false,
                'payload' => json_encode('system'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')->where('group', 'site')->where('name', 'theme')->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only seed if the settings table exists (Spatie Laravel Settings)
        if (! Schema::hasTable('settings')) {
            return;
        }

        DB::table('settings')->insertOrIgnore([
            ['group' => 'page_builder', 'name' => 'enabled', 'locked' => false, 'payload' => json_encode(true)],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'page_builder')
            ->delete();
    }
};

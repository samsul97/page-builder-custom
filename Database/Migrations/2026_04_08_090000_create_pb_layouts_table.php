<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pb_core_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('pb_core_layouts')->insert([
            'key' => 'default',
            'name' => 'Default Core Layout',
            'settings' => json_encode([
                'font_family' => '"Plus Jakarta Sans", sans-serif',
                'heading_font_family' => '"Fraunces", serif',
                'background_color' => '#f7f3ea',
                'card_color' => '#ffffff',
                'accent_color' => '#c46f35',
                'text_color' => '#17261d',
                'muted_text_color' => '#5c6c63',
                'button_radius' => '999px',
                'container_width' => '1200px',
                'section_spacing' => '5rem',
            ]),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('pb_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->unsignedBigInteger('core_layout_id')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pb_layouts');
        Schema::dropIfExists('pb_core_layouts');
    }
};

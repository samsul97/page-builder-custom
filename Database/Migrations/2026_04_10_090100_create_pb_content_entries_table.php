<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pb_content_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_id')->constrained('pb_content_types')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->json('data')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pb_content_entries');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pb_media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('uploads');
            $table->string('directory')->default('page-builder/images');
            $table->string('filename');
            $table->string('original_name');
            $table->string('extension')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('path')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pb_media');
    }
};

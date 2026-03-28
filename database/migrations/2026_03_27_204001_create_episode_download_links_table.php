<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episode_download_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('episode_id')->constrained()->cascadeOnDelete();

            $table->string('name');              // "Download HD"
            $table->string('quality')->nullable(); // "1080p"
            $table->string('size')->nullable();    // "350 MB"

            $table->text('url');

            $table->enum('type', [
                'direct',
                'external',
            ])->default('direct');

            $table->enum('download_sub', [
                'free',
                'premium',
            ])->default('free');

            $table->integer('order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episode_download_links');
    }
};

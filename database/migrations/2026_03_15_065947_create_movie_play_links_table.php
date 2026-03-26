<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_play_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();

            $table->string('name'); // Servidor 1
            $table->string('quality')->nullable(); // 1080p
            $table->integer('order')->default(0); // ordem do player

            $table->text('url'); // link do vídeo

            $table->enum('type', [
                'embed',
                'mp4',
                'm3u8',
                'mkv'
            ]);

            $table->enum('player_sub', [
                'free',
                'premium'
            ])->default('free');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_play_links');
    }
};
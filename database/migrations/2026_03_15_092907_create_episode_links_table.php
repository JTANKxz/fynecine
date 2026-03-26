<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('episode_links', function (Blueprint $table) {

            $table->id();

            $table->foreignId('episode_id')->constrained()->cascadeOnDelete();

            $table->string('name'); // servidor 1
            $table->string('quality')->nullable(); // 1080p
            $table->integer('order')->default(0);

            $table->text('url');

            $table->string('type'); // embed, mp4, m3u8
            $table->enum('player_sub', ['free', 'premium'])->default('free');

            // skip intro
            $table->integer('skip_intro_start')->nullable();
            $table->integer('skip_intro_end')->nullable();

            // skip ending
            $table->integer('skip_ending_start')->nullable();
            $table->integer('skip_ending_end')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episode_links');
    }
};

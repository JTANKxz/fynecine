<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tv_channel_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tv_channel_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->integer('order')->default(0);
            $table->text('url');

            $table->enum('type', ['embed', 'm3u8']);

            $table->enum('player_sub', ['free', 'premium'])->default('free');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tv_channel_links');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify movie_play_links table
        Schema::table('movie_play_links', function (Blueprint $table) {
            $table->enum('type', [
                'embed',
                'mp4',
                'm3u8',
                'mkv',
                'custom'
            ])->change();
        });

        // Modify tv_channel_links table
        Schema::table('tv_channel_links', function (Blueprint $table) {
            $table->enum('type', [
                'embed',
                'm3u8',
                'custom'
            ])->change();
        });
    }

    public function down(): void
    {
        // Revert movie_play_links table
        Schema::table('movie_play_links', function (Blueprint $table) {
            $table->enum('type', [
                'embed',
                'mp4',
                'm3u8',
                'mkv'
            ])->change();
        });

        // Revert tv_channel_links table
        Schema::table('tv_channel_links', function (Blueprint $table) {
            $table->enum('type', [
                'embed',
                'm3u8'
            ])->change();
        });
    }
};

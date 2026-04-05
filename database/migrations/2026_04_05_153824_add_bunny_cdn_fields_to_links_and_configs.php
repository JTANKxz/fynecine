<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. AppConfig settings
        Schema::table('app_configs', function (Blueprint $table) {
            $table->string('bunny_security_key', 255)->nullable();
            $table->string('bunny_cdn_url', 255)->nullable();
        });

        // 2. Movie Play Links additions and type enum update
        Schema::table('movie_play_links', function (Blueprint $table) {
            $table->string('link_path')->nullable();
            $table->integer('expiration_hours')->nullable()->default(4);
            
            // Adding 'private' to the enum
            $table->enum('type', [
                'embed',
                'mp4',
                'm3u8',
                'mkv',
                'custom',
                'private' // New type
            ])->change();
        });

        // 3. Episode Links additions
        Schema::table('episode_links', function (Blueprint $table) {
            $table->string('link_path')->nullable();
            $table->integer('expiration_hours')->nullable()->default(4);
            // type is already a string in episode_links based on earlier view_file
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn(['bunny_security_key', 'bunny_cdn_url']);
        });

        Schema::table('movie_play_links', function (Blueprint $table) {
            $table->dropColumn(['link_path', 'expiration_hours']);
            // Enum revert is tricky in some DBs, usually we leave it or revert to previous known
            $table->enum('type', [
                'embed',
                'mp4',
                'm3u8',
                'mkv',
                'custom'
            ])->change();
        });

        Schema::table('episode_links', function (Blueprint $table) {
            $table->dropColumn(['link_path', 'expiration_hours']);
        });
    }
};

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
        Schema::table('movie_play_links', function (Blueprint $table) {
            $table->string('subtitle_url')->nullable()->after('url');
        });

        Schema::table('episode_links', function (Blueprint $table) {
            $table->string('subtitle_url')->nullable()->after('url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_play_links', function (Blueprint $table) {
            $table->dropColumn('subtitle_url');
        });

        Schema::table('episode_links', function (Blueprint $table) {
            $table->dropColumn('subtitle_url');
        });
    }
};

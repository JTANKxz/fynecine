<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movie_play_links', function (Blueprint $table) {
            $table->enum('client_platform', ['both', 'android', 'web'])
                ->default('both')
                ->after('player_sub');
        });

        Schema::table('episode_links', function (Blueprint $table) {
            $table->enum('client_platform', ['both', 'android', 'web'])
                ->default('both')
                ->after('player_sub');
        });
    }

    public function down(): void
    {
        Schema::table('movie_play_links', function (Blueprint $table) {
            $table->dropColumn('client_platform');
        });

        Schema::table('episode_links', function (Blueprint $table) {
            $table->dropColumn('client_platform');
        });
    }
};

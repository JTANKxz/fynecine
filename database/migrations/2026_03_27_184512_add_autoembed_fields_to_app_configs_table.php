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
        Schema::table('app_configs', function (Blueprint $table) {
            $table->string('autoembed_movie_name')->default('Auto Player');
            $table->string('autoembed_movie_quality')->default('HD');
            $table->string('autoembed_movie_type')->default('embed');
            $table->string('autoembed_movie_player_sub')->default('Dublado/Legendado');

            $table->string('autoembed_serie_name')->default('Auto Player');
            $table->string('autoembed_serie_quality')->default('HD');
            $table->string('autoembed_serie_type')->default('embed');
            $table->string('autoembed_serie_player_sub')->default('Dublado/Legendado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn([
                'autoembed_movie_name', 'autoembed_movie_quality', 'autoembed_movie_type', 'autoembed_movie_player_sub',
                'autoembed_serie_name', 'autoembed_serie_quality', 'autoembed_serie_type', 'autoembed_serie_player_sub'
            ]);
        });
    }
};

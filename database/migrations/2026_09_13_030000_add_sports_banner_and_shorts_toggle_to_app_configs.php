<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->boolean('shorts_enabled')->default(true)->after('is_channels_active');
            $table->boolean('sports_games_banner_enabled')->default(false)->after('shorts_enabled');
            $table->string('sports_games_banner_image')->nullable()->after('sports_games_banner_enabled');
            $table->string('sports_games_banner_url', 1000)->nullable()->after('sports_games_banner_image');
        });
    }

    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn(['shorts_enabled', 'sports_games_banner_enabled', 'sports_games_banner_image', 'sports_games_banner_url']);
        });
    }
};

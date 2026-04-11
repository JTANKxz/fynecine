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
        Schema::table('movie_download_links', function (Blueprint $table) {
            $table->string('link_path')->nullable()->after('url');
            $table->integer('expiration_hours')->default(24)->after('link_path');
        });

        Schema::table('episode_download_links', function (Blueprint $table) {
            $table->string('link_path')->nullable()->after('url');
            $table->integer('expiration_hours')->default(24)->after('link_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_download_links', function (Blueprint $table) {
            $table->dropColumn(['link_path', 'expiration_hours']);
        });

        Schema::table('episode_download_links', function (Blueprint $table) {
            $table->dropColumn(['link_path', 'expiration_hours']);
        });
    }
};

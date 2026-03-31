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
            $table->string('app_version')->nullable();
            $table->string('contact_email')->nullable();
            
            // Refactor autoembed to store multiple sources in JSON
            $table->json('autoembed_movie_sources')->nullable();
            $table->json('autoembed_serie_sources')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn(['app_version', 'contact_email', 'autoembed_movie_sources', 'autoembed_serie_sources']);
        });
    }
};

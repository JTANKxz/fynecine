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
            $table->string('bunny_mp4_key')->nullable()->after('bunny_cdn_url');
            $table->string('bunny_mp4_host')->nullable()->after('bunny_mp4_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn(['bunny_mp4_key', 'bunny_mp4_host']);
        });
    }
};

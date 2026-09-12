<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->string('android_theme_background', 7)->default('#0A0D12')->after('app_name');
            $table->string('android_theme_surface', 7)->default('#10151C')->after('android_theme_background');
            $table->string('android_theme_card', 7)->default('#171E27')->after('android_theme_surface');
            $table->string('android_theme_primary', 7)->default('#8B2FFF')->after('android_theme_card');
            $table->string('android_theme_accent', 7)->default('#00D4FF')->after('android_theme_primary');
        });
    }

    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn([
                'android_theme_background',
                'android_theme_surface',
                'android_theme_card',
                'android_theme_primary',
                'android_theme_accent',
            ]);
        });
    }
};

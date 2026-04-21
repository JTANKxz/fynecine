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
            $table->boolean('is_adult_active')->default(false)->after('rewards_status');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('is_adult_enabled')->default(false)->after('is_kids');
            $table->string('adult_pin', 4)->nullable()->after('is_adult_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn('is_adult_active');
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['is_adult_enabled', 'adult_pin']);
        });
    }
};

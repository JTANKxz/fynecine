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
            $table->boolean('is_channels_active')->default(true)->after('security_mode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn('is_channels_active');
        });
    }
};

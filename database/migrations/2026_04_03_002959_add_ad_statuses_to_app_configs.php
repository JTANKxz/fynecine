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
            $table->boolean('ads_native_status')->default(false)->after('admob_rewarded_id');
            $table->boolean('ads_rewarded_status')->default(false)->after('ads_native_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn(['ads_native_status', 'ads_rewarded_status']);
        });
    }
};

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
            // AdMob IDs
            $table->string('admob_app_id')->nullable();
            $table->string('admob_banner_id')->nullable();
            $table->string('admob_interstitial_id')->nullable();
            $table->string('admob_native_id')->nullable();
            $table->string('admob_rewarded_id')->nullable();

            // Ad Status and Type
            $table->boolean('ads_banner_status')->default(false);
            $table->enum('ads_banner_type', ['admob', 'custom'])->default('admob');
            $table->boolean('ads_interstitial_status')->default(false);
            $table->enum('ads_interstitial_type', ['admob', 'custom'])->default('admob');

            // Custom Ads
            $table->text('custom_banner_image')->nullable(); // Can be URL or file path
            $table->string('custom_banner_link')->nullable();
            $table->enum('custom_interstitial_type', ['image', 'video'])->default('image');
            $table->text('custom_interstitial_media')->nullable(); // URL or file path
            $table->string('custom_interstitial_link')->nullable();
            
            // Interval
            $table->integer('interstitial_interval')->default(3); // Every X actions
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn([
                'admob_app_id',
                'admob_banner_id',
                'admob_interstitial_id',
                'admob_native_id',
                'admob_rewarded_id',
                'ads_banner_status',
                'ads_banner_type',
                'ads_interstitial_status',
                'ads_interstitial_type',
                'custom_banner_image',
                'custom_banner_link',
                'custom_interstitial_type',
                'custom_interstitial_media',
                'custom_interstitial_link',
                'interstitial_interval'
            ]);
        });
    }
};

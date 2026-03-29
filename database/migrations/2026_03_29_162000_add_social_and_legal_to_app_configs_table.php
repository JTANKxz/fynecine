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
            $table->string('instagram_url')->nullable();
            $table->boolean('is_instagram_active')->default(false);
            
            $table->string('telegram_url')->nullable();
            $table->boolean('is_telegram_active')->default(false);
            
            $table->string('whatsapp_url')->nullable();
            $table->boolean('is_whatsapp_active')->default(false);
            
            $table->longText('terms_of_use')->nullable();
            $table->longText('privacy_policy')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn([
                'instagram_url',
                'is_instagram_active',
                'telegram_url',
                'is_telegram_active',
                'whatsapp_url',
                'is_whatsapp_active',
                'terms_of_use',
                'privacy_policy',
            ]);
        });
    }
};

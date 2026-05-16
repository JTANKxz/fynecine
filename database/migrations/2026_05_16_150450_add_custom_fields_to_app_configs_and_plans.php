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
            $table->string('default_avatar_kids_url')->nullable()->after('default_avatar_kids');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->boolean('is_popular')->default(false)->after('is_active');
            $table->decimal('offer_price', 10, 2)->nullable()->after('price');
            $table->timestamp('offer_expires_at')->nullable()->after('offer_price');
            $table->string('discount_label')->nullable()->after('offer_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn('default_avatar_kids_url');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['is_popular', 'offer_price', 'offer_expires_at', 'discount_label']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cakto_campaign_purchases', function (Blueprint $table) {
            $table->foreignId('claimed_by_user_id')->nullable()->after('buyer_email')->constrained('users')->nullOnDelete();
            $table->timestamp('activated_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('cakto_campaign_purchases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('claimed_by_user_id');
            $table->dropColumn('activated_at');
        });
    }
};

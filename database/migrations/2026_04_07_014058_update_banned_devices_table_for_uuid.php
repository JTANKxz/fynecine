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
        Schema::table('banned_devices', function (Blueprint $table) {
            $table->string('device_uuid')->nullable()->after('device_id')->index();
            $table->timestamp('expires_at')->nullable()->after('ban_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banned_devices', function (Blueprint $table) {
            $table->dropColumn(['device_uuid', 'expires_at']);
        });
    }
};

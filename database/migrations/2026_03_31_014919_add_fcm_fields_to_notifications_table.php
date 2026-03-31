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
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('big_picture_url')->nullable()->after('image_url');
            $table->string('push_status')->default('pending')->after('expires_at'); // pending, sent, failed
            $table->string('segment')->default('all')->after('push_status'); // all, premium, basic, free, guest, individual
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            //
        });
    }
};

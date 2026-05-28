<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tv_channel_links', function (Blueprint $table) {
            $table->string('type', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tv_channel_links', function (Blueprint $table) {
            $table->enum('type', ['embed', 'm3u8', 'custom'])->change();
        });
    }
};

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
        Schema::table('adult_media', function (Blueprint $table) {
            $table->string('player_type')->default('direct')->after('type'); // direct, hls, embed, sniffer
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adult_media', function (Blueprint $table) {
            //
        });
    }
};

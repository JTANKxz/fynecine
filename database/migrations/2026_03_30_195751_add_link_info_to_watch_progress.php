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
        Schema::table('watch_progress', function (Blueprint $table) {
            $table->string('link_id')->nullable()->after('episode_id');
            $table->string('link_type')->nullable()->after('link_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('watch_progress', function (Blueprint $table) {
            $table->dropColumn(['link_id', 'link_type']);
        });
    }
};

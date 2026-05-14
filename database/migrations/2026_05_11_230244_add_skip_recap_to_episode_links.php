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
        Schema::table('episode_links', function (Blueprint $table) {
            $table->integer('skip_recap_start')->nullable()->after('skip_intro_end');
            $table->integer('skip_recap_end')->nullable()->after('skip_recap_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('episode_links', function (Blueprint $table) {
            $table->dropColumn(['skip_recap_start', 'skip_recap_end']);
        });
    }
};

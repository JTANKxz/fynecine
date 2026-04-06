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
        Schema::table('movies', function (Blueprint $table) {
            $table->string('tag_text')->nullable()->after('overview');
            $table->timestamp('tag_expires_at')->nullable()->after('tag_text');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->string('tag_text')->nullable()->after('overview');
            $table->timestamp('tag_expires_at')->nullable()->after('tag_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn(['tag_text', 'tag_expires_at']);
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn(['tag_text', 'tag_expires_at']);
        });
    }
};

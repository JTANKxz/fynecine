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
            $table->text('excluded_autoembeds')->nullable()->after('use_autoembed');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->text('excluded_autoembeds')->nullable()->after('use_autoembed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('excluded_autoembeds');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('excluded_autoembeds');
        });
    }
};

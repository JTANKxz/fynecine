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
            $table->string('age_rating', 10)->nullable();
        });

        Schema::table('series', function (Blueprint $table) {
            $table->string('age_rating', 10)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('age_rating');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('age_rating');
        });
    }
};

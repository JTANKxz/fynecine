<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->decimal('rating', 3, 1)->nullable()->after('runtime');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->decimal('rating', 3, 1)->nullable()->after('number_of_episodes');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('rating');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};
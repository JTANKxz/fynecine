<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->string('logo_path', 1000)->nullable()->after('backdrop_path');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->string('logo_path', 1000)->nullable()->after('backdrop_path');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropColumn('logo_path');
        });
    }
};

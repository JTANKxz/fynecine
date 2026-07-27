<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tmdb_keywords', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tmdb_id')->unique();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('keywordables', function (Blueprint $table) {
            $table->foreignId('tmdb_keyword_id')->constrained('tmdb_keywords')->cascadeOnDelete();
            $table->morphs('keywordable');
            $table->primary(['tmdb_keyword_id', 'keywordable_id', 'keywordable_type'], 'keywordables_primary');
        });
        Schema::table('movies', fn (Blueprint $table) => $table->unsignedInteger('vote_count')->default(0)->after('rating'));
        Schema::table('series', fn (Blueprint $table) => $table->unsignedInteger('vote_count')->default(0)->after('rating'));
        Schema::table('app_configs', fn (Blueprint $table) => $table->unsignedTinyInteger('tmdb_cast_limit')->default(10)->after('tmdb_key'));
    }

    public function down(): void
    {
        Schema::dropIfExists('keywordables');
        Schema::dropIfExists('tmdb_keywords');
        Schema::table('movies', fn (Blueprint $table) => $table->dropColumn('vote_count'));
        Schema::table('series', fn (Blueprint $table) => $table->dropColumn('vote_count'));
        Schema::table('app_configs', fn (Blueprint $table) => $table->dropColumn('tmdb_cast_limit'));
    }
};

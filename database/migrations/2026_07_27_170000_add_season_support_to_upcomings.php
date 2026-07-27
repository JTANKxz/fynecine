<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upcomings', function (Blueprint $table) {
            $table->string('external_key')->nullable()->unique()->after('tmdb_id');
            $table->foreignId('linked_serie_id')->nullable()->after('external_key')->constrained('series')->nullOnDelete();
            $table->unsignedInteger('season_number')->nullable()->after('linked_serie_id');
            $table->string('season_name')->nullable()->after('season_number');
        });
        DB::table('upcomings')->orderBy('id')->each(function ($item) {
            DB::table('upcomings')->where('id', $item->id)->update(['external_key' => ($item->type === 'series' ? 'tv:' : 'movie:') . $item->tmdb_id]);
        });
    }

    public function down(): void
    {
        Schema::table('upcomings', function (Blueprint $table) {
            $table->dropForeign(['linked_serie_id']);
            $table->dropUnique(['external_key']);
            $table->dropColumn(['external_key', 'linked_serie_id', 'season_number', 'season_name']);
        });
    }
};

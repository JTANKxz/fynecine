<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->unsignedInteger('current_season_num')->nullable()->after('last_synced_at');
            $table->string('current_season_name')->nullable()->after('current_season_num');
            $table->integer('current_stage_num')->nullable()->after('current_season_name');
            $table->string('current_stage_name')->nullable()->after('current_stage_num');
            $table->unsignedTinyInteger('stage_type')->nullable()->after('current_stage_name');
            $table->boolean('has_standings')->default(false)->after('stage_type');
            $table->boolean('has_live_standings')->default(false)->after('has_standings');
            $table->boolean('has_current_stage_standings')->default(false)->after('has_live_standings');
            $table->boolean('has_brackets')->default(false)->after('has_current_stage_standings');
            $table->boolean('has_stats')->default(false)->after('has_brackets');
            $table->string('provider_color', 20)->nullable()->after('has_stats');
        });

        DB::table('championships')
            ->where('external_provider', '365scores')
            ->where('external_id', '113')
            ->update([
                'current_season_num' => 76,
                'current_season_name' => '2026',
                'current_stage_num' => 1,
                'current_stage_name' => 'Temporada Regular',
                'stage_type' => 1,
                'has_standings' => true,
                'has_live_standings' => true,
                'has_current_stage_standings' => true,
                'has_brackets' => false,
                'has_stats' => true,
                'provider_color' => '#01479A',
            ]);
    }

    public function down(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->dropColumn([
                'current_season_num', 'current_season_name', 'current_stage_num', 'current_stage_name',
                'stage_type', 'has_standings', 'has_live_standings', 'has_current_stage_standings',
                'has_brackets', 'has_stats', 'provider_color',
            ]);
        });
    }
};

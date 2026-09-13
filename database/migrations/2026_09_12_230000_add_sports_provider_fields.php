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
            $table->string('external_provider', 50)->nullable()->after('name');
            $table->string('external_id', 100)->nullable()->after('external_provider');
            $table->unsignedInteger('sport_id')->nullable()->after('external_id');
            $table->unsignedInteger('country_id')->nullable()->after('sport_id');
            $table->boolean('is_sports_enabled')->default(false)->after('country_id');
            $table->boolean('auto_sync')->default(false)->after('is_sports_enabled');
            $table->unsignedInteger('display_order')->default(0)->after('auto_sync');
            $table->timestamp('last_synced_at')->nullable()->after('display_order');
            $table->unique(['external_provider', 'external_id'], 'championships_external_source_unique');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->string('external_provider', 50)->nullable()->after('image_url');
            $table->string('external_id', 100)->nullable()->after('external_provider');
            $table->unique(['external_provider', 'external_id'], 'teams_external_source_unique');
        });

        DB::table('championships')
            ->where('name', 'Brasileirão Série A')
            ->update([
                'external_provider' => '365scores',
                'external_id' => '113',
                'sport_id' => 1,
                'country_id' => 21,
                'is_sports_enabled' => true,
                'auto_sync' => true,
                'display_order' => 1,
            ]);
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropUnique('teams_external_source_unique');
            $table->dropColumn(['external_provider', 'external_id']);
        });

        Schema::table('championships', function (Blueprint $table) {
            $table->dropUnique('championships_external_source_unique');
            $table->dropColumn([
                'external_provider', 'external_id', 'sport_id', 'country_id',
                'is_sports_enabled', 'auto_sync', 'display_order', 'last_synced_at',
            ]);
        });
    }
};

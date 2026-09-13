<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('external_provider', 50)->nullable()->after('championship_id');
            $table->string('external_id', 100)->nullable()->after('external_provider');
            $table->unique(['external_provider', 'external_id'], 'events_external_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropUnique('events_external_source_unique');
            $table->dropColumn(['external_provider', 'external_id']);
        });
    }
};

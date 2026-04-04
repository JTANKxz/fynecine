<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_url')->nullable();
            $table->timestamps();
        });

        // Add optional team references to events
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('home_team_id')->nullable()->after('away_team')->constrained('teams')->nullOnDelete();
            $table->foreignId('away_team_id')->nullable()->after('home_team_id')->constrained('teams')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['home_team_id']);
            $table->dropForeign(['away_team_id']);
            $table->dropColumn(['home_team_id', 'away_team_id']);
        });

        Schema::dropIfExists('teams');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // O índice único antigo também cobre o FK de short_id no MySQL.
        // Criamos cobertura simples antes de removê-lo, sem soltar a FK.
        Schema::table('short_interactions', function (Blueprint $table) {
            $table->index('short_id', 'short_interactions_short_id_index');
        });

        Schema::table('short_interactions', function (Blueprint $table) {
            $table->dropUnique('short_profile_interaction_unique');
            $table->unsignedTinyInteger('watch_percentage')->nullable()->after('watch_seconds');
            $table->uuid('session_id')->nullable()->after('watch_percentage');
            $table->index(['short_id', 'profile_id', 'type', 'created_at'], 'short_interaction_timeline_index');
        });

        Schema::create('short_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_id')->unique()->constrained('shorts')->cascadeOnDelete();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('unique_viewers')->default(0);
            $table->unsignedBigInteger('likes')->default(0);
            $table->unsignedBigInteger('skips')->default(0);
            $table->unsignedBigInteger('replays')->default(0);
            $table->unsignedBigInteger('completions')->default(0);
            $table->unsignedBigInteger('total_watch_seconds')->default(0);
            $table->decimal('average_completion', 5, 2)->default(0);
            $table->decimal('engagement_score', 10, 2)->default(0);
            $table->timestamp('last_engagement_at')->nullable();
            $table->timestamps();
            $table->index(['engagement_score', 'last_engagement_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_metrics');
        Schema::table('short_interactions', function (Blueprint $table) {
            $table->dropIndex('short_interaction_timeline_index');
            $table->dropIndex('short_interactions_short_id_index');
            $table->dropColumn(['watch_percentage', 'session_id']);
            $table->unique(['short_id', 'profile_id', 'type'], 'short_profile_interaction_unique');
        });
    }
};

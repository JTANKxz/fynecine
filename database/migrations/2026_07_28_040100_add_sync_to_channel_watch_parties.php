<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('channel_watch_parties', function (Blueprint $table) {
            if (!Schema::hasColumn('channel_watch_parties', 'is_playing')) $table->boolean('is_playing')->default(true);
            if (!Schema::hasColumn('channel_watch_parties', 'playback_position')) $table->decimal('playback_position', 12, 3)->nullable();
            if (!Schema::hasColumn('channel_watch_parties', 'state_updated_at')) $table->timestamp('state_updated_at')->nullable();
        });
        if (!Schema::hasTable('channel_watch_party_members')) {
            Schema::create('channel_watch_party_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('channel_watch_party_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamp('joined_at');
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamp('left_at')->nullable();
                $table->timestamps();
            });
        }
        Schema::table('channel_watch_party_members', function (Blueprint $table) {
            $table->unique(['channel_watch_party_id', 'user_id'], 'cwpm_party_user_unique');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('channel_watch_party_members');
        Schema::table('channel_watch_parties', function (Blueprint $table) { $table->dropColumn(['is_playing','playback_position','state_updated_at']); });
    }
};

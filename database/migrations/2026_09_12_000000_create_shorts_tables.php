<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shorts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('source_provider', 32); // direct, youtube, dailymotion, tiktok, google_drive
            $table->string('source_url', 2048);
            $table->string('external_id', 128)->nullable();
            $table->string('playback_kind', 20)->default('direct'); // direct or embed
            $table->string('embed_url', 2048)->nullable();
            $table->string('thumbnail_url', 2048)->nullable();
            $table->string('duration_seconds', 12)->nullable();
            $table->string('hashtags', 500)->nullable();
            $table->string('category', 80)->nullable();
            $table->string('language', 12)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->string('availability', 20)->default('pending');
            $table->text('availability_message')->nullable();
            $table->json('provider_metadata')->nullable();
            $table->timestamps();
            $table->index(['is_active', 'published_at']);
            $table->index(['source_provider', 'availability']);
        });

        Schema::create('short_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_id')->constrained('shorts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // like, hide, save, report, impression, completion
            $table->unsignedInteger('watch_seconds')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['short_id', 'profile_id', 'type'], 'short_profile_interaction_unique');
            $table->index(['profile_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_interactions');
        Schema::dropIfExists('shorts');
    }
};

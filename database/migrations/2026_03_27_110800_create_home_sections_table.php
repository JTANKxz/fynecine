<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['custom', 'genre', 'trending', 'network']);
            $table->enum('content_type', ['movie', 'series', 'both'])->default('both');

            $table->foreignId('genre_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('network_id')->nullable();

            $table->enum('trending_period', ['today', 'week', 'all_time'])->nullable();

            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('limit')->default(15);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};

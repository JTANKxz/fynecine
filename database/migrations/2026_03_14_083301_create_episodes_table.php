<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('series_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();

            $table->integer('episode_number'); // ordem do episódio
            $table->integer('tmdb_id')->nullable();

            $table->string('name');
            $table->text('overview')->nullable();

            $table->integer('duration')->nullable(); // minutos

            $table->string('still_path')->nullable(); // capa

            $table->enum('status', ['active','inactive'])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
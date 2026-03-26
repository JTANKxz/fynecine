<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('seasons', function (Blueprint $table) {

            $table->id();

            $table->foreignId('series_id')->constrained()->cascadeOnDelete();

            $table->integer('season_number'); // numero da temporada
            $table->integer('tmdb_id')->nullable();

            $table->enum('status', ['active','inactive'])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seasons');
    }
};

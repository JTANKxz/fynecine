<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movies', function (Blueprint $table) {

            $table->id();

            // Identificadores externos
            $table->unsignedBigInteger('tmdb_id')->unique();
            $table->string('imdb_id')->nullable();

            // Conteúdo
            $table->string('title');
            $table->string('slug')->unique();

            $table->year('release_year')->nullable();
            $table->integer('runtime')->nullable();

            $table->text('overview')->nullable();

            // Imagens
            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();

            // Tipo de conteúdo (filme / série futuramente)
            $table->string('content_type')->default('movie');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movies');
    }
};
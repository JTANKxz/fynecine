<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('genres', function (Blueprint $table) {

            $table->id();

            // ID do gênero no TMDB
            $table->unsignedInteger('tmdb_id')->unique();

            // Nome do gênero
            $table->string('name');

            // Slug para URLs
            $table->string('slug')->unique();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('genres');
    }
};
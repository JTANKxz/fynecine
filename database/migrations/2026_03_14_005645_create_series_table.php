<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('series', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('tmdb_id')->unique();
            $table->string('name');
            $table->string('slug')->unique();

            $table->integer('first_air_year')->nullable();
            $table->integer('last_air_year')->nullable();

            $table->integer('number_of_seasons')->nullable();
            $table->integer('number_of_episodes')->nullable();

            $table->text('overview')->nullable();

            $table->string('poster_path')->nullable();
            $table->string('backdrop_path')->nullable();

            $table->string('trailer_key')->nullable();
            $table->string('trailer_url')->nullable();
            $table->string('content_type')->default('series');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('series');
    }
};

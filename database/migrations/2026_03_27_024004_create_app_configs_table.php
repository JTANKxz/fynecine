<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_configs', function (Blueprint $table) {
            $table->id();
            
            // App Info e Chaves
            $table->string('app_name')->default('Superflix');
            $table->string('tmdb_key')->nullable();
            $table->string('api_token_key')->nullable();
            $table->text('custom_message')->nullable();
            
            // Login e Onboarding
            $table->boolean('force_login')->default(false);
            $table->boolean('show_onboarding')->default(true);
            
            // Modo de Segurança (Aprovação Loja)
            $table->boolean('security_mode')->default(false);

            // Atualização do App
            $table->enum('update_type', ['none', 'in_app', 'external'])->default('none');
            $table->string('update_url')->nullable();
            $table->boolean('update_skippable')->default(true);

            // Autoembed Dinâmico
            $table->boolean('autoembed_movies')->default(true);
            $table->boolean('autoembed_series')->default(true);
            $table->string('autoembed_movie_url')->nullable()->default('https://superflixapi.rest/movie/{tmdb_id}');
            $table->string('autoembed_serie_url')->nullable()->default('https://superflixapi.rest/serie/{tmdb_id}/{season}/{episode}');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_configs');
    }
};

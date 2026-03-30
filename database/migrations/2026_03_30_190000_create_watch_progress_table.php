<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('watch_progress', function (Blueprint $table) {
            $table->id();
            
            // Referência ao usuário ou guest
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('guest_id')->nullable()->index(); // UUID para usuários não logados
            
            // Conteúdo sendo assistido
            $table->string('content_id')->index(); // ID do filme/série (tmdb_id, slug, etc)
            $table->enum('content_type', ['movie', 'episode'])->default('movie');
            
            // Progresso
            $table->unsignedBigInteger('progress')->default(0); // segundos
            $table->unsignedBigInteger('duration')->default(0); // segundos
            
            // Para episódios
            $table->unsignedInteger('season_id')->nullable();
            $table->unsignedInteger('episode_id')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Índices compostos para performance
            $table->unique(['user_id', 'content_id', 'content_type'], 'unique_user_content_progress');
            $table->unique(['guest_id', 'content_id', 'content_type'], 'unique_guest_content_progress');
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_progress');
    }
};

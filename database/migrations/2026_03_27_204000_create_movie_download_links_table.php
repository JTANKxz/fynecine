<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movie_download_links', function (Blueprint $table) {
            $table->id();

            $table->foreignId('movie_id')->constrained()->cascadeOnDelete();

            $table->string('name');             // "Download HD", "Servidor 1"
            $table->string('quality')->nullable(); // "1080p", "720p", "4K"
            $table->string('size')->nullable();    // "2.1 GB", "800 MB"

            $table->text('url');               // Link do arquivo/redirecionamento

            $table->enum('type', [
                'direct',   // link direto (mp4, mkv, etc.) — interno
                'external', // redireciona para site externo
            ])->default('direct');

            $table->enum('download_sub', [
                'free',
                'premium',
            ])->default('free');

            $table->integer('order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_download_links');
    }
};

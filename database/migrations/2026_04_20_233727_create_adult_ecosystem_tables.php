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
        // 1. Categorias Adultas
        Schema::create('adult_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Modelos / Performers
        Schema::create('adult_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('biography')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('cover_url')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Galerias
        Schema::create('adult_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adult_model_id')->nullable()->constrained('adult_models')->onDelete('set null');
            $table->foreignId('adult_category_id')->nullable()->constrained('adult_categories')->onDelete('set null');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_url')->nullable();
            $table->enum('type', ['photo', 'video', 'both'])->default('photo');
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 4. Mídia da Galeria (Fotos/Vídeos individuais)
        Schema::create('adult_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adult_gallery_id')->constrained('adult_galleries')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('url'); // Suporte a URLs longas
            $table->enum('type', ['image', 'video'])->default('image');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 5. Seções da Home Adulta
        Schema::create('adult_home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type'); // 'trending', 'recent', 'models', 'galleries', 'categories', 'custom'
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('limit')->default(15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adult_home_sections');
        Schema::dropIfExists('adult_media');
        Schema::dropIfExists('adult_galleries');
        Schema::dropIfExists('adult_models');
        Schema::dropIfExists('adult_categories');
    }
};

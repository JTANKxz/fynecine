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
        // 1. Coleções Adultas
        Schema::create('adult_collections', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('cover_url')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Atualizar Galerias
        Schema::table('adult_galleries', function (Blueprint $table) {
            if (!Schema::hasColumn('adult_galleries', 'adult_collection_id')) {
                $table->foreignId('adult_collection_id')->nullable()->after('adult_category_id')->constrained('adult_collections')->onDelete('set null');
            }
            
            // Note: We keep 'collection' column for now or drop it later if empty. 
            // The user wants real entities now.
        });

        // 3. Atualizar Mídia (permitir solo/avulsa)
        Schema::table('adult_media', function (Blueprint $table) {
            $table->unsignedBigInteger('adult_gallery_id')->nullable()->change();
            
            // Adicionar campos extras para mídia avulsa ser categorizada/modelada opcionalmente
            $table->foreignId('adult_model_id')->nullable()->after('adult_gallery_id')->constrained('adult_models')->onDelete('set null');
            $table->foreignId('adult_category_id')->nullable()->after('adult_model_id')->constrained('adult_categories')->onDelete('set null');
            $table->boolean('is_active')->default(true)->after('type');
        });

        // 4. Itens Manuais por Seção (Custom)
        Schema::create('adult_home_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adult_home_section_id')->constrained('adult_home_sections')->onDelete('cascade');
            $table->string('item_type'); // 'gallery', 'media', 'model', 'collection'
            $table->unsignedBigInteger('item_id');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adult_home_section_items');
        
        Schema::table('adult_media', function (Blueprint $table) {
            $table->dropForeign(['adult_category_id']);
            $table->dropForeign(['adult_model_id']);
            $table->dropColumn(['adult_category_id', 'adult_model_id', 'is_active']);
            $table->unsignedBigInteger('adult_gallery_id')->nullable(false)->change();
        });

        Schema::table('adult_galleries', function (Blueprint $table) {
            $table->dropForeign(['adult_collection_id']);
            $table->dropColumn('adult_collection_id');
        });

        Schema::dropIfExists('adult_collections');
    }
};

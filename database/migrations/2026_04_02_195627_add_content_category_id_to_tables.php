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
        Schema::table('sliders', function (Blueprint $table) {
            $table->foreignId('content_category_id')->nullable()->constrained()->onDelete('set null');
        });

        Schema::table('home_sections', function (Blueprint $table) {
            $table->foreignId('content_category_id')->nullable()->constrained()->onDelete('set null');
        });

        Schema::table('movies', function (Blueprint $table) {
            $table->foreignId('content_category_id')->nullable()->constrained()->onDelete('set null');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->foreignId('content_category_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sliders', function (Blueprint $table) {
            $table->dropForeign(['content_category_id']);
            $table->dropColumn('content_category_id');
        });

        Schema::table('home_sections', function (Blueprint $table) {
            $table->dropForeign(['content_category_id']);
            $table->dropColumn('content_category_id');
        });

        Schema::table('movies', function (Blueprint $table) {
            $table->dropForeign(['content_category_id']);
            $table->dropColumn('content_category_id');
        });

        Schema::table('series', function (Blueprint $table) {
            $table->dropForeign(['content_category_id']);
            $table->dropColumn('content_category_id');
        });
    }
};

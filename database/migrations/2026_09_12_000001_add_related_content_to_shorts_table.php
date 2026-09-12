<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shorts', function (Blueprint $table) {
            $table->string('related_type', 20)->nullable()->after('category'); // movie, series, season
            $table->unsignedBigInteger('related_id')->nullable()->after('related_type');
            $table->string('related_title', 255)->nullable()->after('related_id');
            $table->index(['related_type', 'related_id']);
        });
    }
    public function down(): void
    {
        Schema::table('shorts', function (Blueprint $table) {
            $table->dropIndex(['related_type', 'related_id']);
            $table->dropColumn(['related_type', 'related_id', 'related_title']);
        });
    }
};

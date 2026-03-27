<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_id');
            $table->enum('content_type', ['movie', 'series']);
            $table->timestamp('viewed_at')->useCurrent();

            $table->index(['content_type', 'content_id', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_views');
    }
};

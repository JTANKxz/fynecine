<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('content_id');
            $table->enum('content_type', ['movie', 'series']);

            $table->unique(['network_id', 'content_id', 'content_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_content');
    }
};

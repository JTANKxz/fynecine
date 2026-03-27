<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tv_channel_category_channel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tv_channel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tv_channel_category_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tv_channel_category_channel');
    }
};

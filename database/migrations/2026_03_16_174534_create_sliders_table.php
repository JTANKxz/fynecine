<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('content_id');
            $table->enum('content_type', ['movie', 'series']);

            $table->integer('position')->default(0);
            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};

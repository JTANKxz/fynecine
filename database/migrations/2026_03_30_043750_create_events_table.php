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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('home_team')->nullable();
            $table->string('away_team')->nullable();
            $table->string('image_url')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('start_time');
            $table->index('end_time');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

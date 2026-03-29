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
        Schema::create('avatars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('avatar_category_id');
            $table->string('image');
            $table->timestamps();

            $table->foreign('avatar_category_id')->references('id')->on('avatar_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avatars');
    }
};

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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('image_url')->nullable();
            
            // Ação: none, url, movie, series, plans
            $table->string('action_type')->default('none');
            $table->string('action_value')->nullable();
            
            $table->boolean('is_global')->default(false);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index('is_global');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

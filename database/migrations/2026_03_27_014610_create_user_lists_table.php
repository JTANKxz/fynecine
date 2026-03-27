<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Polimórfico: suporta Movie e Serie
            $table->unsignedBigInteger('listable_id');
            $table->string('listable_type'); // App\Models\Movie | App\Models\Serie

            $table->timestamps();

            // Evita duplicatas: mesmo usuário não pode adicionar o mesmo item duas vezes
            $table->unique(['user_id', 'listable_id', 'listable_type']);

            $table->index(['listable_id', 'listable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lists');
    }
};

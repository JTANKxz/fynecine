<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('castables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cast_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->morphs('castable');
            // cria:
            // castable_id
            // castable_type

            $table->string('character')->nullable();
            $table->integer('order')->default(0);

            $table->timestamps();

            // ✅ INDEX (coloca aqui)
            $table->index(['castable_id', 'castable_type']);

            // evita duplicação
            $table->unique([
                'cast_id',
                'castable_id',
                'castable_type'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('castables');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Limpar tabela existente para evitar conflitos de FK
        if (Schema::hasTable('user_lists')) {
            \Illuminate\Support\Facades\DB::table('user_lists')->truncate();
        }

        Schema::table('user_lists', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'listable_id', 'listable_type']);
            $table->renameColumn('user_id', 'profile_id');
        });

        Schema::rename('user_lists', 'profile_lists');

        Schema::table('profile_lists', function (Blueprint $table) {
            $table->foreign('profile_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->unique(['profile_id', 'listable_id', 'listable_type']);
        });
    }

    public function down(): void
    {
        Schema::table('profile_lists', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropUnique(['profile_id', 'listable_id', 'listable_type']);
            $table->renameColumn('profile_id', 'user_id');
        });

        Schema::rename('profile_lists', 'user_lists');

        Schema::table('user_lists', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['user_id', 'listable_id', 'listable_type']);
        });
    }
};

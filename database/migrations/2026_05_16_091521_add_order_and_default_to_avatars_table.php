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
        Schema::table('avatar_categories', function (Blueprint $table) {
            $table->integer('display_order')->default(0)->after('name');
        });

        Schema::table('avatars', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('image');
            $table->boolean('is_kids')->default(false)->after('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('avatar_categories', function (Blueprint $table) {
            $table->dropColumn('display_order');
        });

        Schema::table('avatars', function (Blueprint $table) {
            $table->dropColumn(['is_default', 'is_kids']);
        });
    }
};

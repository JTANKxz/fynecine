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
        Schema::table('app_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('default_avatar_p1')->nullable()->after('is_adult_active');
            $table->unsignedBigInteger('default_avatar_p2')->nullable()->after('default_avatar_p1');
            $table->unsignedBigInteger('default_avatar_p3')->nullable()->after('default_avatar_p2');
            $table->unsignedBigInteger('default_avatar_p4')->nullable()->after('default_avatar_p3');
            $table->unsignedBigInteger('default_avatar_p5')->nullable()->after('default_avatar_p4');
            $table->unsignedBigInteger('default_avatar_p6')->nullable()->after('default_avatar_p5');
            $table->unsignedBigInteger('default_avatar_kids')->nullable()->after('default_avatar_p6');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn([
                'default_avatar_p1',
                'default_avatar_p2',
                'default_avatar_p3',
                'default_avatar_p4',
                'default_avatar_p5',
                'default_avatar_p6',
                'default_avatar_kids'
            ]);
        });
    }
};

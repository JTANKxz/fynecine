<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('pin', 4)->nullable();
            $table->boolean('is_main')->default(false);
        });

        // Set the first profile of each user to be the main profile automatically
        DB::statement('
            UPDATE profiles p1
            JOIN (
                SELECT user_id, MIN(id) as first_id
                FROM profiles
                GROUP BY user_id
            ) p2 ON p1.id = p2.first_id
            SET p1.is_main = 1
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['pin', 'is_main']);
        });
    }
};


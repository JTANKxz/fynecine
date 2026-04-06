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
        Schema::create('championships', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->unsignedBigInteger('championship_id')->nullable()->after('id');
            $table->foreign('championship_id')->references('id')->on('championships')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['championship_id']);
            $table->dropColumn('championship_id');
        });

        Schema::dropIfExists('championships');
    }
};

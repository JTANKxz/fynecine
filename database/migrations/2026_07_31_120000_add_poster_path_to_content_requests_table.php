<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('content_requests', function (Blueprint $table) {
            $table->string('poster_path', 500)->nullable()->after('year');
        });
    }

    public function down(): void
    {
        Schema::table('content_requests', function (Blueprint $table) {
            $table->dropColumn('poster_path');
        });
    }
};
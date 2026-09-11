<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('content_type', 20)->nullable()->after('subtopic');
            $table->unsignedBigInteger('content_id')->nullable()->after('content_type');
            $table->string('content_title')->nullable()->after('content_id');
            $table->string('content_poster', 2048)->nullable()->after('content_title');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn(['content_type', 'content_id', 'content_title', 'content_poster']);
        });
    }
};

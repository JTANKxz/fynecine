<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('watch_progress', function (Blueprint $table) {
            $table->longText('link_headers')->nullable()->after('link_url');
            $table->longText('link_cookies')->nullable()->after('link_headers');
        });
    }

    public function down(): void
    {
        Schema::table('watch_progress', function (Blueprint $table) {
            $table->dropColumn(['link_headers', 'link_cookies']);
        });
    }
};

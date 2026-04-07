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
            $table->boolean('maintenance_mode')->default(false)->after('security_mode');
            $table->string('maintenance_title')->nullable()->after('maintenance_mode');
            $table->text('maintenance_description')->nullable()->after('maintenance_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_configs', function (Blueprint $table) {
            $table->dropColumn(['maintenance_mode', 'maintenance_title', 'maintenance_description']);
        });
    }
};

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
        $tables = ['movie_play_links', 'episode_links', 'tv_channel_links', 'event_links'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    if (!Schema::hasColumn($table->getTable(), 'user_agent')) {
                        $table->string('user_agent')->nullable()->after('url');
                    }
                    if (!Schema::hasColumn($table->getTable(), 'referer')) {
                        $table->string('referer')->nullable()->after('user_agent');
                    }
                    if (!Schema::hasColumn($table->getTable(), 'origin')) {
                        $table->string('origin')->nullable()->after('referer');
                    }
                    if (!Schema::hasColumn($table->getTable(), 'cookie')) {
                        $table->text('cookie')->nullable()->after('origin');
                    }

                    // Campos Bunny para as tabelas que ainda não possuem
                    if ($table->getTable() === 'tv_channel_links' || $table->getTable() === 'event_links') {
                        if (!Schema::hasColumn($table->getTable(), 'link_path')) {
                            $table->string('link_path')->nullable()->after('cookie');
                        }
                        if (!Schema::hasColumn($table->getTable(), 'expiration_hours')) {
                            $table->integer('expiration_hours')->default(4)->after('link_path');
                        }
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['movie_play_links', 'episode_links', 'tv_channel_links', 'event_links'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn(['user_agent', 'referer', 'origin', 'cookie', 'link_path', 'expiration_hours']);
                });
            }
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE epg_programs MODIFY icon_url TEXT NULL');
        DB::statement('ALTER TABLE epg_channels MODIFY icon_url TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE epg_programs MODIFY icon_url VARCHAR(255) NULL');
        DB::statement('ALTER TABLE epg_channels MODIFY icon_url VARCHAR(255) NULL');
    }
};

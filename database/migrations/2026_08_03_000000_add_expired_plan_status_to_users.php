<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY plan_type ENUM('free', 'basic', 'premium', 'expired') NOT NULL DEFAULT 'free'");
        }

        DB::table('users')
            ->whereIn('plan_type', ['basic', 'premium'])
            ->whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<=', now())
            ->update([
                'plan_type' => 'expired',
                'features' => json_encode([]),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('users')->where('plan_type', 'expired')->update([
            'plan_type' => 'free',
            'features' => json_encode([]),
            'updated_at' => now(),
        ]);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY plan_type ENUM('free', 'basic', 'premium') NOT NULL DEFAULT 'free'");
        }
    }
};
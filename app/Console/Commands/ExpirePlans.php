<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ExpirePlans extends Command
{
    protected $signature = 'plans:expire';

    protected $description = 'Marca como expirados os planos pagos cuja validade terminou';

    public function handle(): int
    {
        $count = User::query()
            ->whereIn('plan_type', ['basic', 'premium'])
            ->whereNotNull('plan_expires_at')
            ->where('plan_expires_at', '<=', now())
            ->update([
                'plan_type' => 'expired',
                'features' => json_encode([]),
                'updated_at' => now(),
            ]);

        $this->info("{$count} plano(s) marcado(s) como expirado(s).");

        return self::SUCCESS;
    }
}
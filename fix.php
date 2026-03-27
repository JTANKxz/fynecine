<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    Schema::table('coupons', function (Blueprint $table) {
        if (Schema::hasColumn('coupons', 'subscription_plan_id')) {
            // Tentar dropar a FK tbm caso exista
            try { $table->dropForeign(['subscription_plan_id']); } catch (\Exception $e) {}
            $table->dropColumn('subscription_plan_id');
        }
    });
} catch (\Exception $e) {
    echo "Erro ao dropar coluna: " . $e->getMessage() . "\n";
}

try {
    Schema::dropIfExists('subscription_plans');
} catch (\Exception $e) {
    echo "Erro ao dropar tabela plans: " . $e->getMessage() . "\n";
}

DB::table('migrations')->where('migration', 'like', '%subscription_plan%')->delete();
echo "Limpo!\n";

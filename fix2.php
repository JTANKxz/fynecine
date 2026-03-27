<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

try {
    Schema::table('coupons', function (Blueprint $table) {
        if (Schema::hasColumn('coupons', 'subscription_plan_id')) {
            try { $table->dropForeign(['subscription_plan_id']); } catch (\Exception $e) {}
            $table->dropColumn('subscription_plan_id');
        }
    });
} catch (\Exception $e) {}

try {
    Schema::dropIfExists('subscription_plans');
} catch (\Exception $e) {}

DB::table('migrations')->where('migration', 'like', '%subscription_plan%')->delete();
echo "Limpo!\n";

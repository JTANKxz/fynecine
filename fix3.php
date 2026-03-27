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
        $table->dropForeign(['subscription_plan_id']);
    });
} catch (\Exception $e) { echo "No FK to drop.\n"; }

try {
    Schema::table('coupons', function (Blueprint $table) {
        $table->dropColumn('subscription_plan_id');
    });
    echo "Column dropped.\n";
} catch (\Exception $e) { echo "Column drop failed: " . $e->getMessage() . "\n"; }

try {
    Schema::dropIfExists('subscription_plans');
} catch (\Exception $e) { echo "Table drop failed: " . $e->getMessage() . "\n"; }

DB::table('migrations')->where('migration', 'like', '%subscription_plan%')->delete();
echo "Limpo!\n";

<?php

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Mail::raw('SMTP test message', function ($message) {
        $message->to('valdeci.ds@gmail.com')
                ->subject('SMTP Test');
    });
    echo "SMTP Test successful!\n";
} catch (\Exception $e) {
    echo "SMTP Test failed: " . $e->getMessage() . "\n";
}

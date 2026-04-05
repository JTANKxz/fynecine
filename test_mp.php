<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$payment = \App\Models\PixPayment::latest()->first();
echo "LOCAL ID: " . $payment->id . "\n";
echo "LOCAL STATUS: " . $payment->status . "\n";

$response = \Illuminate\Support\Facades\Http::withToken(config('services.mercadopago.token'))
    ->get('https://api.mercadopago.com/v1/payments/' . $payment->mp_payment_id);

echo "MP STATUS: " . ($response->json()['status'] ?? 'N/A') . "\n";

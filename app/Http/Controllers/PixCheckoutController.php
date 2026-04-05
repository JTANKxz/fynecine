<?php

namespace App\Http\Controllers;

use App\Models\PixPayment;

class PixCheckoutController extends Controller
{
    /**
     * Exibe a página de checkout PIX para a WebView do app.
     * GET /pix/checkout/{pixPayment}
     */
    public function show(int $pixPaymentId)
    {
        $payment = PixPayment::with('plan')->findOrFail($pixPaymentId);

        return view('pix.checkout', [
            'payment' => $payment,
            'plan' => $payment->plan,
            'apiToken' => \App\Models\AppConfig::getSettings()->api_token_key,
        ]);
    }
}

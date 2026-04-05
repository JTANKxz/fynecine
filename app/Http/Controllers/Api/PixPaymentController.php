<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PixPayment;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Common\RequestOptions;

class PixPaymentController extends Controller
{
    public function __construct()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.token'));
    }

    /**
     * Cria um pagamento PIX via Mercado Pago.
     * POST /api/pix/create
     */
    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        if (!$plan->is_active) {
            return response()->json(['message' => 'Este plano não está disponível.'], 422);
        }

        // Cancela qualquer PIX "pendente" anterior para gerar sempre um novo limpo
        PixPayment::where('user_id', $user->id)
            ->where('subscription_plan_id', $plan->id)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);

        try {
            $client = new PaymentClient();

            $requestOptions = new RequestOptions();
            $requestOptions->setCustomHeaders([
                "X-Idempotency-Key: " . uniqid('pix_', true)
            ]);

            $paymentData = [
                "transaction_amount" => (float) $plan->price,
                "description" => "Plano {$plan->name} - {$plan->duration_days} dias",
                "payment_method_id" => "pix",
                "payer" => [
                    "email" => $user->email,
                    "first_name" => $user->name,
                ]
            ];

            $payment = $client->create($paymentData, $requestOptions);

            // Salvar no banco
            $pixPayment = PixPayment::create([
                'user_id' => $user->id,
                'subscription_plan_id' => $plan->id,
                'mp_payment_id' => $payment->id,
                'amount' => $plan->price,
                'status' => 'pending',
                'pix_qr_code' => $payment->point_of_interaction->transaction_data->qr_code ?? null,
                'pix_qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null,
                'pix_ticket_url' => $payment->point_of_interaction->transaction_data->ticket_url ?? null,
                'expires_at' => Carbon::now()->addMinutes(30),
            ]);

            return response()->json([
                'payment_id' => $pixPayment->id,
                'checkout_url' => url("/pix/checkout/{$pixPayment->id}"),
                'expires_at' => $pixPayment->expires_at->toISOString(),
            ]);

        } catch (\Exception $e) {
            \Log::error('MercadoPago PIX Error', [
                'message' => $e->getMessage(),
                'user_id' => $user->id,
                'plan_id' => $plan->id,
            ]);

            return response()->json([
                'message' => 'Erro ao gerar pagamento PIX. Tente novamente.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Consulta o status do pagamento PIX.
     * GET /api/pix/status/{paymentId}
     */
    public function status(int $paymentId): JsonResponse
    {
        $payment = PixPayment::with(['plan', 'user'])->findOrFail($paymentId);

        // Sempre consultar a API do MP se estiver pendente, independente de estar expirado localmente
        if ($payment->isPending()) {
            try {
                $client = new PaymentClient();
                $mpPayment = $client->get($payment->mp_payment_id);
                
                if ($mpPayment && $mpPayment->status === 'approved') {
                    $payment->update([
                        'status' => 'approved',
                        'paid_at' => Carbon::now(),
                    ]);
                    
                    // Atribui o plano
                    $user = $payment->user;
                    $plan = $payment->plan;
                    
                    if ($user && $plan) {
                        $currentExpires = $user->plan_expires_at && $user->plan_expires_at->isFuture()
                            ? $user->plan_expires_at
                            : Carbon::now();

                        $user->plan_type = $plan->plan_type;
                        $user->plan_expires_at = $currentExpires->copy()->addDays($plan->duration_days);
                        $user->features = $plan->features;
                        $user->save();
                    }
                    
                    \Log::info('Polling: MercadoPago PIX Approved', ['payment_id' => $payment->id]);
                } elseif ($mpPayment && in_array($mpPayment->status, ['rejected', 'cancelled'])) {
                    $payment->update(['status' => 'rejected']);
                } elseif ($payment->isExpired()) {
                    // MP ainda diz pending ou não processou, mas nossa janela expirou
                    $payment->update(['status' => 'cancelled']);
                }
            } catch (\Exception $e) {
                \Log::error('PixPaymentController::status MP Check Error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return response()->json([
            'status' => $payment->status,
            'plan_name' => $payment->plan->name ?? null,
            'paid_at' => $payment->paid_at?->toISOString(),
        ]);
    }
}

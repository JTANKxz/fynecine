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

        // Pega qualquer pagamento anterior pendente
        $existingPayment = PixPayment::where('user_id', $user->id)
            ->where('subscription_plan_id', $plan->id)
            ->where('status', 'pending')
            ->first();

        if ($existingPayment) {
            // Antes de cancelar, vamos conferir no MP se o usuário não acabou de pagar ele!
            try {
                $client = new PaymentClient();
                $mpPayment = $client->get($existingPayment->mp_payment_id);
                
                if ($mpPayment && $mpPayment->status === 'approved') {
                    // O usuário pagou enquanto o app estava fechado! Ativa o plano dele
                    $existingPayment->update([
                        'status' => 'approved',
                        'paid_at' => Carbon::now(),
                    ]);
                    
                    $currentExpires = $user->plan_expires_at && $user->plan_expires_at->isFuture()
                        ? $user->plan_expires_at
                        : Carbon::now();

                    $user->plan_type = $plan->plan_type;
                    $user->plan_expires_at = $currentExpires->copy()->addDays($plan->duration_days);
                    $user->features = $plan->features;
                    $user->save();

                    // Retorna o checkout antigo, que vai carregar e dar a tela de Sucesso Verde imediatamente.
                    return response()->json([
                        'payment_id' => $existingPayment->id,
                        'checkout_url' => url("/pix/checkout/{$existingPayment->id}"),
                        'expires_at' => $existingPayment->expires_at->toISOString(),
                    ]);
                }
            } catch (\Exception $e) { }

            // Se consultamos o MP e de fato AINDA não foi pago, cancelamos localmente para gerar o novo que o usuário pediu
            $existingPayment->update(['status' => 'cancelled']);
        }

        // 1. Criar o registro local PRIMEIRO para gerar o ID de referência interna (external_reference)
        $pixPayment = PixPayment::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'amount' => $plan->price,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);

        try {
            $client = new PaymentClient();

            $requestOptions = new RequestOptions();
            $requestOptions->setCustomHeaders([
                "X-Idempotency-Key: " . uniqid('pix_', true)
            ]);

            // Split name for MP recommendation
            $nameParts = explode(' ', trim($user->name));
            $firstName = $nameParts[0] ?? 'Cliente';
            $lastName = count($nameParts) > 1 ? end($nameParts) : 'FyneCine';

            $paymentData = [
                "transaction_amount" => (float) $plan->price,
                "description" => "Plano {$plan->name} - {$plan->duration_days} dias",
                "payment_method_id" => "pix",
                "external_reference" => (string) $pixPayment->id, // Ação Obrigatória (14 pts)
                "notification_url" => url('/api/webhooks/mercadopago'), // Ação Obrigatória (Webhook)
                "statement_descriptor" => "FYNE CINE PLANO", // Recomendado
                "binary_mode" => true, // Recomendado para aprovação instantânea
                "payer" => [
                    "email" => $user->email,
                    "first_name" => $firstName,
                    "last_name" => $lastName, // Recomendado
                ],
                "additional_info" => [
                    "items" => [
                        [
                            "id" => (string) $plan->id,
                            "title" => "Plano " . $plan->name,
                            "description" => $plan->duration_days . " dias de acesso VIP",
                            "category_id" => "services",
                            "quantity" => 1,
                            "unit_price" => (float) $plan->price
                        ]
                    ]
                ]
            ];

            $payment = $client->create($paymentData, $requestOptions);

            // 2. Atualizar o registro local com os dados do Mercado Pago
            $pixPayment->update([
                'mp_payment_id' => $payment->id,
                'pix_qr_code' => $payment->point_of_interaction->transaction_data->qr_code ?? null,
                'pix_qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null,
                'pix_ticket_url' => $payment->point_of_interaction->transaction_data->ticket_url ?? null,
            ]);

            return response()->json([
                'payment_id' => $pixPayment->id,
                'checkout_url' => url("/pix/checkout/{$pixPayment->id}"),
                'expires_at' => $pixPayment->expires_at->toISOString(),
            ]);

        } catch (\Exception $e) {
            // Em caso de erro no MP, removemos o registro local "limpo" ou marcamos como falha
            $pixPayment->delete();

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

        // Se já estiver aprovado no banco (via webhook ou poll anterior), retorna imediatamente.
        // Isso torna o polling extremamente rápido e responsivo.
        if ($payment->status === 'approved') {
            return response()->json([
                'status' => 'approved',
                'plan_name' => $payment->plan->name ?? null,
                'paid_at' => $payment->paid_at?->toISOString(),
            ]);
        }

        // Se estiver pendente, decidimos se consultamos o Mercado Pago agora ou esperamos o próximo ciclo.
        // Consultamos o MP se:
        // 1. Nunca foi consultado (updated_at == created_at)
        // 2. A última consulta/atualização foi há mais de 10 segundos
        $shouldCheckMP = $payment->isPending() && (
            $payment->updated_at->eq($payment->created_at) || 
            $payment->updated_at->diffInSeconds(Carbon::now()) > 10
        );

        if ($shouldCheckMP) {
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
                    
                    \Log::info('Polling: MercadoPago PIX Approved (Synced)', ['payment_id' => $payment->id]);
                } elseif ($mpPayment && in_array($mpPayment->status, ['rejected', 'cancelled'])) {
                    $payment->update(['status' => 'rejected']);
                } elseif ($payment->isExpired()) {
                    $payment->update(['status' => 'cancelled']);
                } else {
                    // Apenas atualiza o 'updated_at' para resetar o timer de 10s do polling
                    $payment->touch();
                }
            } catch (\Exception $e) {
                \Log::error('PixPaymentController::status MP Check Error', [
                    'message' => $e->getMessage(),
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

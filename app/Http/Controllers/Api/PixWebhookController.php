<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PixPayment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;

class PixWebhookController extends Controller
{
    /**
     * Recebe notificações de pagamento do Mercado Pago.
     * POST /api/webhooks/mercadopago
     * 
     * ROTA PÚBLICA — sem auth:sanctum
     */
    public function handle(Request $request): JsonResponse
    {
        \Log::info('MercadoPago Webhook Received', $request->all());

        // O MP envia notificações de tipo "payment"
        $type = $request->input('type') ?? $request->input('topic');
        $dataId = $request->input('data.id') ?? $request->input('id');

        if ($type !== 'payment' || !$dataId) {
            return response()->json(['status' => 'ignored']);
        }

        try {
            // Consulta o pagamento diretamente no MP para validar o status real
            MercadoPagoConfig::setAccessToken(config('services.mercadopago.token'));
            $client = new PaymentClient();
            $mpPayment = $client->get($dataId);

            if (!$mpPayment) {
                \Log::warning('MercadoPago Webhook: Payment not found', ['id' => $dataId]);
                return response()->json(['status' => 'not_found'], 404);
            }

            // Busca o pagamento local
            $pixPayment = PixPayment::where('mp_payment_id', $mpPayment->id)->first();

            if (!$pixPayment) {
                \Log::warning('MercadoPago Webhook: Local payment not found', ['mp_id' => $mpPayment->id]);
                return response()->json(['status' => 'not_found'], 404);
            }

            // Já processado
            if ($pixPayment->status === 'approved') {
                return response()->json(['status' => 'already_processed']);
            }

            $mpStatus = $mpPayment->status; // approved, pending, rejected, cancelled, etc.

            if ($mpStatus === 'approved') {
                $pixPayment->update([
                    'status' => 'approved',
                    'paid_at' => Carbon::now(),
                ]);

                // Ativar o plano do usuário
                $this->activateUserPlan($pixPayment);

                \Log::info('MercadoPago PIX Approved', [
                    'payment_id' => $pixPayment->id,
                    'user_id' => $pixPayment->user_id,
                    'plan_id' => $pixPayment->subscription_plan_id,
                ]);

            } elseif (in_array($mpStatus, ['rejected', 'cancelled', 'refunded'])) {
                $pixPayment->update(['status' => 'rejected']);
            }

            return response()->json(['status' => 'ok']);

        } catch (\Exception $e) {
            \Log::error('MercadoPago Webhook Error', [
                'message' => $e->getMessage(),
                'data_id' => $dataId,
            ]);

            return response()->json(['status' => 'error'], 500);
        }
    }

    /**
     * Ativa o plano do usuário após pagamento aprovado.
     */
    private function activateUserPlan(PixPayment $pixPayment): void
    {
        $user = $pixPayment->user;
        $plan = $pixPayment->plan;

        if (!$user || !$plan) {
            return;
        }

        // Se o usuário já tem um plano ativo e não expirou, soma os dias
        $currentExpires = $user->plan_expires_at && $user->plan_expires_at->isFuture()
            ? $user->plan_expires_at
            : Carbon::now();

        $newExpires = $currentExpires->copy()->addDays($plan->duration_days);

        $user->plan_type = $plan->plan_type;
        $user->plan_expires_at = $newExpires;
        $user->features = $plan->features;
        $user->save();
    }
}

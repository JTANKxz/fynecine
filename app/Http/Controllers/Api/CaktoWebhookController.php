<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CaktoCampaignPurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CaktoWebhookController extends Controller
{
    /**
     * Receives only the Cakto purchase_approved event for the app campaign.
     * This route intentionally stays outside api.token and Sanctum middleware.
     */
    public function handle(Request $request): JsonResponse
    {
        $webhooks = (array) config('services.cakto.campaign_webhooks', []);
        if ($webhooks === []) {
            Log::critical('Cakto webhook is not configured.');
            return response()->json(['message' => 'Cakto integration is not configured.'], 503);
        }

        // Cakto may omit the Content-Type header. Decode the raw body first so
        // data.id, customer and product are available in either delivery format.
        $rawPayload = json_decode($request->getContent(), true);
        $payload = is_array($rawPayload) && $rawPayload !== []
            ? $rawPayload
            : ($request->json()->all() ?: $request->all());
        $event = (string) data_get($payload, 'event', data_get($payload, 'event_type', data_get($payload, 'type', 'purchase_approved')));
        if ($event !== 'purchase_approved') {
            return response()->json(['status' => 'ignored']);
        }

        $campaignPlan = $this->campaignPlanForRequest($request, $webhooks, $payload);
        if ($campaignPlan === null) {
            Log::warning('Cakto webhook rejected: invalid secret.', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $productId = (string) $this->firstValue($payload, [
            'product.id', 'product_id', 'data.product.id', 'data.product_id', 'purchase.product.id',
        ]);
        $purchaseId = (string) $this->firstValue($payload, [
            'purchase.id', 'order.id', 'transaction.id', 'data.purchase.id', 'data.order.id', 'id',
        ]);
        if ($purchaseId === '') {
            Log::warning('Cakto webhook rejected: purchase id missing.', ['product_id' => $productId]);
            return response()->json(['message' => 'Purchase id is required.'], 422);
        }

        $email = $this->firstValue($payload, [
            'customer.email', 'buyer.email', 'data.customer.email', 'data.buyer.email', 'email',
        ]);
        $name = $this->firstValue($payload, [
            'customer.name', 'buyer.name', 'data.customer.name', 'data.buyer.name', 'name',
        ]);

        CaktoCampaignPurchase::updateOrCreate(
            ['cakto_purchase_id' => $purchaseId],
            [
                'product_id' => $productId ?: 'cakto-' . $campaignPlan,
                'buyer_name' => $name ?: null,
                'buyer_email' => $email ?: null,
                'status' => 'approved:' . $campaignPlan,
                'payload' => $payload,
                'approved_at' => now(),
            ]
        );

        return response()->json(['status' => 'accepted']);
    }

    private function campaignPlanForRequest(Request $request, array $webhooks, array $payload): ?string
    {
        foreach ($webhooks as $plan => $secret) {
            if (is_string($secret) && $secret !== '' && $this->hasValidSecret($request, $secret, $payload)) {
                return (string) $plan;
            }
        }

        return null;
    }

    private function hasValidSecret(Request $request, string $secret, array $payload = []): bool
    {
        $authorization = (string) $request->header('Authorization');
        $bearer = Str::startsWith($authorization, 'Bearer ') ? Str::after($authorization, 'Bearer ') : null;
        $received = $request->header('X-Cakto-Webhook-Secret')
            ?? $request->header('X-Webhook-Secret')
            ?? $request->header('X-Cakto-Secret')
            ?? $bearer
            ?? data_get($payload, 'secret')
            ?? $request->input('secret')
            ?? data_get($request->all(), 'data.secret');

        return is_string($received) && hash_equals($secret, $received);
    }

    private function firstValue(array $payload, array $paths): ?string
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);
            if (is_scalar($value) && (string) $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Lista os planos ativos disponíveis para o aplicativo.
     * Rota pública: GET /api/plans
     */
    public function plans(): JsonResponse
    {
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        $formattedPlans = $plans->map(function ($plan) {
            
            // Features now handled by formatPlanFeatures
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'plan_type' => $plan->plan_type,
                'plan_category' => $plan->plan_category,
                'price' => $plan->price,
                'original_price' => $plan->original_price,
                'discount_percentage' => $plan->original_price > $plan->price 
                    ? round((1 - ($plan->price / $plan->original_price)) * 100) 
                    : 0,
                'duration_days' => $plan->duration_days,
                'features' => $this->formatPlanFeatures($plan->plan_type, $plan->features ?? []),
                'raw_features' => $plan->features
            ];
        });

        return response()->json($formattedPlans);
    }

    /**
     * Resgata um cupom para ativar a assinatura (Basic ou Premium)
     */
    public function redeem(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string']
        ]);

        $user = $request->user();
        
        // 1. Busca cupom válido ignorando case
        $code = strtoupper($request->code);
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['message' => 'Cupom inválido ou inexistente.'], 404);
        }

        if (!$coupon->isValid()) {
            return response()->json(['message' => 'Cupom esgotado ou inativo.'], 403);
        }

        // 2. Verifica se o usuário já usou este cupom
        if ($user->coupons()->where('coupon_id', $coupon->id)->exists()) {
            return response()->json(['message' => 'Você já resgatou este cupom.'], 403);
        }

        // 3. Calcula nova data de expiração, pegando os dias e features do Plano Vinculado ou do código legado
        $daysToAdd = $coupon->subscription_plan_id ? $coupon->subscriptionPlan->duration_days : $coupon->days;
        $planType = $coupon->subscription_plan_id ? $coupon->subscriptionPlan->plan_type : $coupon->plan;
        
        // As features também vêm do plano se ele existir, mesclando com as do próprio cupom (Sobrescrita inteligente)
        $planFeatures = $coupon->subscription_plan_id ? ($coupon->subscriptionPlan->features ?? []) : [];
        $couponFeatures = $coupon->features ?? [];
        
        // Combina os dois arrays e remove duplicatas
        $features = array_unique(array_merge($planFeatures, $couponFeatures));

        $currentExpires = $user->plan_expires_at && $user->plan_expires_at->isFuture()
            ? $user->plan_expires_at
            : Carbon::now();

        $newExpires = $currentExpires->copy()->addDays($daysToAdd);
        
        $user->plan_type = $planType;
        $user->plan_expires_at = $newExpires;
        $user->features = $features;
        $user->save();

        // 4. Registra uso e abate do contador max_uses
        $coupon->increment('used_count');
        $user->coupons()->attach($coupon->id);

        return response()->json([
            'message' => "Cupom ativado com sucesso! Você agora é {$planType}.",
            'plan_type' => $user->plan_type,
            'expires_at' => $user->plan_expires_at,
            'features' => $this->formatPlanFeatures($planType, $features)
        ]);
    }

    /**
     * Formata os benefícios do plano no padrão esperado pelo App.
     */
    private function formatPlanFeatures(string $type, array $features): array
    {
        $features = collect($features);
        $comparisonFeatures = collect();

        // 1. Jogos ao Vivo (O ponto chave)
        $comparisonFeatures->push([
            'name' => 'Jogos e Eventos Ao Vivo',
            'included' => $type === 'premium'
        ]);

        // 2. Perfis
        $maxProfiles = ($type === 'premium') ? 6 : (($type === 'basic') ? 3 : 1);
        $comparisonFeatures->push([
            'name' => "Até {$maxProfiles} perfis de usuário",
            'included' => true
        ]);

        // 3. Quotas Diárias (Requests e Support)
        $quota = ($type === 'premium') ? 5 : (($type === 'basic') ? 3 : 1);
        $comparisonFeatures->push([
            'name' => "{$quota} Pedidos de filmes diários",
            'included' => true
        ]);

        $comparisonFeatures->push([
            'name' => "{$quota} Suporte prioritário diário",
            'included' => true
        ]);

        // 4. Anúncios
        $comparisonFeatures->push([
            'name' => 'Navegação Sem anúncios',
            'included' => $features->contains('no_ads') || $type === 'premium'
        ]);

        // 5. Canais
        $comparisonFeatures->push([
            'name' => 'Canais de TV (IPTV)',
            'included' => $features->contains('premium_channels') || $type === 'premium'
        ]);

        return $comparisonFeatures->values()->toArray();
    }
}

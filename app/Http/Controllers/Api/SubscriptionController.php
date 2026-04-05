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
            $type = $plan->plan_type;
            $features = collect($plan->features ?? []);
            $readableFeatures = collect();

            // 1. Perfis
            $maxProfiles = ($type === 'premium') ? 6 : (($type === 'basic') ? 3 : 1);
            $readableFeatures->push("✅ Até {$maxProfiles} perfis de usuário");

            // 2. Anúncios
            if ($features->contains('no_ads')) {
                $readableFeatures->push("✅ Sem anúncios (Interface Limpa)");
            }

            // 3. Eventos Ao Vivo
            if ($type === 'premium') {
                $readableFeatures->push("✅ Jogos e Eventos Ao Vivo");
            } else {
                $readableFeatures->push("❌ Sem Eventos Ao Vivo");
            }

            // 4. Canais
            if ($features->contains('premium_channels')) {
                $readableFeatures->push("✅ Todos os Canais de TV (IPTV)");
            }

            // 5. Quotas Diárias
            $quota = ($type === 'premium') ? 5 : (($type === 'basic') ? 3 : 1);
            $readableFeatures->push("✅ {$quota} Pedidos de filmes diários");
            $readableFeatures->push("✅ {$quota} Suporte prioritário diário");

            // 6. Diferenciais do Banco (se houver extras)
            if ($features->contains('priority_support') && $type !== 'premium') {
                $readableFeatures->push("✅ Suporte Prioritário");
            }

            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'plan_type' => $plan->plan_type,
                'price' => $plan->price,
                'duration_days' => $plan->duration_days,
                'features' => $readableFeatures->values(),
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
        $coupon = Coupon::where('code', $request->code)->first();

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
            'features' => $user->features
        ]);
    }
}

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
                'offer_price' => $plan->offer_price,
                'offer_expires_at' => $plan->offer_expires_at,
                'discount_label' => $plan->discount_label,
                'is_popular' => (bool) $plan->is_popular,
                'first_time_discount' => $plan->first_time_discount,
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
        $comparisonFeatures = collect();

        foreach ($features as $feature) {
            // Handle new JSON format (Array of associative arrays)
            if (is_array($feature) && isset($feature['name'])) {
                $comparisonFeatures->push([
                    'name' => $feature['name'],
                    'included' => (bool) ($feature['included'] ?? false)
                ]);
            } 
            // Handle legacy format (Array of strings)
            else if (is_string($feature)) {
                $name = $feature;
                if ($feature === 'no_ads') $name = 'Assistir Sem Anúncios';
                else if ($feature === 'priority_support') $name = 'Suporte Prioritário';
                else if ($feature === 'priority_requests') $name = 'Fazer Pedidos (TMDB)';
                else if ($feature === 'premium_channels') $name = 'Canais de TV Fechada VIP';
                
                $comparisonFeatures->push([
                    'name' => $name,
                    'included' => true
                ]);
            }
        }
        
        // If the features are completely empty (maybe not configured yet), fallback to a basic structure
        if ($comparisonFeatures->isEmpty()) {
            $comparisonFeatures->push([
                'name' => 'Catálogo Completo',
                'included' => true
            ]);
            $comparisonFeatures->push([
                'name' => 'Navegação Sem Anúncios',
                'included' => $type === 'premium'
            ]);
        }

        return $comparisonFeatures->values()->toArray();
    }
}

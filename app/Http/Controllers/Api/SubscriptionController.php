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
            ->get([
                'id', 
                'name', 
                'plan_type', 
                'price', 
                'duration_days', 
                'features'
            ]);

        return response()->json($plans);
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
        
        // As features também vêm do plano se ele existir, senão do próprio cupom
        $features = $coupon->subscription_plan_id ? $coupon->subscriptionPlan->features : $coupon->features;

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

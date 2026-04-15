<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RewardClaim;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewardController extends Controller
{
    /**
     * Retorna o status de recompensas do usuário.
     */
    public function status()
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'points' => 0,
                'can_claim' => false,
                'streak' => 0,
                'plans' => $this->getRedeemablePlans(),
            ]);
        }

        $today = Carbon::today()->toDateString();
        $alreadyClaimed = RewardClaim::where('user_id', $user->id)
            ->where('claimed_date', $today)
            ->exists();

        // Calcular streak (dias consecutivos)
        $streak = $this->calculateStreak($user->id);

        return response()->json([
            'points' => $user->reward_points ?? 0,
            'can_claim' => !$alreadyClaimed,
            'streak' => $streak,
            'plans' => $this->getRedeemablePlans(),
        ]);
    }

    /**
     * Registra o check-in diário (após assistir o anúncio).
     */
    public function claim(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $today = Carbon::today()->toDateString();

        // Verifica se já resgatou hoje
        $alreadyClaimed = RewardClaim::where('user_id', $user->id)
            ->where('claimed_date', $today)
            ->exists();

        if ($alreadyClaimed) {
            return response()->json([
                'error' => 'Você já resgatou seu ponto hoje. Volte amanhã!',
                'points' => $user->reward_points,
                'can_claim' => false,
            ], 429);
        }

        // Registrar claim
        RewardClaim::create([
            'user_id' => $user->id,
            'claimed_date' => $today,
        ]);

        // Incrementar pontos
        $user->increment('reward_points');
        $user->refresh();

        $streak = $this->calculateStreak($user->id);

        return response()->json([
            'message' => 'Ponto resgatado com sucesso!',
            'points' => $user->reward_points,
            'can_claim' => false,
            'streak' => $streak,
        ]);
    }

    /**
     * Troca pontos por um plano premium.
     */
    public function redeem(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'plan_id' => 'required|integer|exists:subscription_plans,id',
        ]);

        $plan = SubscriptionPlan::where('id', $request->plan_id)
            ->where('is_active', true)
            ->whereNotNull('points_cost')
            ->where('points_cost', '>', 0)
            ->first();

        if (!$plan) {
            return response()->json(['error' => 'Plano não disponível para resgate.'], 404);
        }

        if ($user->reward_points < $plan->points_cost) {
            return response()->json([
                'error' => 'Pontos insuficientes.',
                'points' => $user->reward_points,
                'required' => $plan->points_cost,
            ], 422);
        }

        // Deduzir pontos
        $user->decrement('reward_points', $plan->points_cost);

        // Ativar o plano
        $expiresAt = Carbon::now()->addDays($plan->duration_days);

        // Se já tem plano ativo, estende
        if ($user->hasPlan() && $user->plan_expires_at && $user->plan_expires_at->isFuture()) {
            $expiresAt = $user->plan_expires_at->addDays($plan->duration_days);
        }

        $user->update([
            'plan_type' => $plan->plan_type,
            'plan_expires_at' => $expiresAt,
        ]);

        $user->refresh();

        return response()->json([
            'message' => "Plano {$plan->name} ativado com sucesso!",
            'points' => $user->reward_points,
            'plan_type' => $user->plan_type,
            'plan_expires_at' => $user->plan_expires_at?->toIso8601String(),
        ]);
    }

    /**
     * Calcula streak de dias consecutivos.
     */
    private function calculateStreak(int $userId): int
    {
        $claims = RewardClaim::where('user_id', $userId)
            ->orderBy('claimed_date', 'desc')
            ->limit(365)
            ->pluck('claimed_date')
            ->map(fn($d) => Carbon::parse($d)->toDateString());

        if ($claims->isEmpty()) return 0;

        $streak = 0;
        $checkDate = Carbon::today();

        foreach ($claims as $claimDate) {
            if ($claimDate === $checkDate->toDateString()) {
                $streak++;
                $checkDate->subDay();
            } elseif ($claimDate === $checkDate->subDay()->toDateString()) {
                // Allow checking yesterday if today hasn't been claimed yet
                $streak++;
                $checkDate->subDay();
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Retorna planos que podem ser trocados por pontos.
     */
    private function getRedeemablePlans(): array
    {
        return SubscriptionPlan::where('is_active', true)
            ->whereNotNull('points_cost')
            ->where('points_cost', '>', 0)
            ->orderBy('points_cost', 'asc')
            ->get(['id', 'name', 'plan_type', 'duration_days', 'points_cost', 'features'])
            ->toArray();
    }
}

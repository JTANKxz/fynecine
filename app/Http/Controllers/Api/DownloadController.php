<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DownloadLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DownloadController extends Controller
{
    /**
     * Limites diários de download por tipo de plano.
     */
    private const LIMITS = [
        'free'    => 3,
        'basic'   => PHP_INT_MAX,
        'premium' => PHP_INT_MAX,
    ];

    /**
     * Registra um download e verifica o limite diário do usuário.
     *
     * POST /api/downloads/log
     * Body: { "content_id": 5, "content_type": "movie" }
     */
    public function log(Request $request): JsonResponse
    {
        $request->validate([
            'content_id'   => ['required', 'integer'],
            'content_type' => ['required', 'in:movie,episode'],
        ]);

        $user = $request->user();

        // Determina o limite baseado no plano
        $planType = $user->plan_type ?? 'free';
        $hasPlan  = $user->hasPlan();

        if (!$hasPlan) {
            $planType = 'free';
        }

        $dailyLimit = self::LIMITS[$planType] ?? self::LIMITS['free'];

        // Conta downloads de hoje
        $todayCount = DownloadLog::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= $dailyLimit) {
            return response()->json([
                'allowed'          => false,
                'message'          => "Você atingiu o limite diário de {$dailyLimit} downloads. Faça um upgrade para baixar mais.",
                'limit'            => $dailyLimit,
                'used'             => $todayCount,
                'remaining'        => 0,
                'upgrade_required' => true,
            ], 429);
        }

        // Registra o download
        DownloadLog::create([
            'user_id'      => $user->id,
            'content_id'   => $request->content_id,
            'content_type' => $request->content_type,
            'ip'           => $request->ip(),
        ]);

        $remaining = $dailyLimit === PHP_INT_MAX ? null : $dailyLimit - ($todayCount + 1);

        return response()->json([
            'allowed'   => true,
            'message'   => 'Download registrado.',
            'limit'     => $dailyLimit === PHP_INT_MAX ? 'ilimitado' : $dailyLimit,
            'used'      => $todayCount + 1,
            'remaining' => $remaining,
        ]);
    }

    /**
     * Retorna o status de downloads do usuário (quantos já usou hoje).
     * GET /api/downloads/status
     */
    public function status(Request $request): JsonResponse
    {
        $user     = $request->user();
        $planType = ($user->hasPlan() && $user->plan_type) ? $user->plan_type : 'free';
        $limit    = self::LIMITS[$planType] ?? self::LIMITS['free'];

        $todayCount = DownloadLog::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->count();

        return response()->json([
            'plan_type'  => $planType,
            'limit'      => $limit === PHP_INT_MAX ? 'ilimitado' : $limit,
            'used_today' => $todayCount,
            'remaining'  => $limit === PHP_INT_MAX ? null : max(0, $limit - $todayCount),
            'can_download' => $todayCount < $limit,
        ]);
    }
}

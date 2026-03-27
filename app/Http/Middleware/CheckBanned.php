<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\BannedDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Checa conta banida
        if (Auth::guard('sanctum')->check()) {
            $user = Auth::guard('sanctum')->user();
            
            if ($user->banned_at) {
                // Logout automático por segurança (se for Laravel nativo)
                // O Sanctum token já ficaria inválido ao resetarmos mas garantimos com abort
                return response()->json([
                    'message' => 'Sua conta foi banida permanentemente.',
                    'reason' => $user->ban_reason
                ], 403);
            }
        }

        // 2. Checa IP Banido
        $ip = $request->ip();
        if ($ip && BannedDevice::where('ip_address', $ip)->exists()) {
            return response()->json([
                'message' => 'Seu acesso foi revogado.',
            ], 403);
        }

        // 3. Checa Device ID (Se o Frontend mandar header Device-Id)
        $deviceId = $request->header('Device-Id');
        if ($deviceId && BannedDevice::where('device_id', $deviceId)->exists()) {
            return response()->json([
                'message' => 'Seu aparelho foi banido.',
            ], 403);
        }

        return $next($request);
    }
}

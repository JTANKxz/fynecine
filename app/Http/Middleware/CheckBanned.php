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
        $user = Auth::guard('sanctum')->user();
        $token = $request->user()?->currentAccessToken();

        // 1. Checa conta banida
        if ($user && $user->banned_at) {
            return response()->json([
                'message' => 'Sua conta foi banida permanentemente.',
                'reason' => $user->ban_reason
            ], 403);
        }

        // 2. Checa IP Banido
        $ip = $request->ip();
        if ($ip && BannedDevice::where('ip_address', $ip)->exists()) {
            return response()->json(['message' => 'Seu acesso foi revogado.'], 403);
        }

        // 3. Captura UUID do Header ou Token
        $headerUuid = $request->header('X-Device-Uuid');
        $tokenUuid = $token?->device_uuid;
        $activeUuid = $tokenUuid ?: $headerUuid;

        // Auto-Sync: Se o token existe mas não tem UUID (sessão antiga), salva agora
        if ($token && !$tokenUuid && $headerUuid) {
            $this->syncTokenMetadata($token, $request, $headerUuid);
        }

        // 4. Checa Banimento por UUID (Prioridade) ou Device ID antigo
        $deviceId = $request->header('Device-Id');
        
        $isBanned = false;
        if ($activeUuid) {
            $isBanned = BannedDevice::where('device_uuid', $activeUuid)
                ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->exists();
        }

        if (!$isBanned && $deviceId) {
            $isBanned = BannedDevice::where('device_id', $deviceId)->exists();
        }

        if ($isBanned) {
            // Se estiver logado, deleta o token para expulsar imediatamente
            if ($token) {
                $token->delete();
            }

            return response()->json([
                'status' => false,
                'message' => 'Este dispositivo foi banido da plataforma.',
                'error_code' => 'DEVICE_BANNED'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Sincroniza metadados para tokens que não os possuem (retrocompatibilidade).
     */
    private function syncTokenMetadata($token, Request $request, string $uuid): void
    {
        $ip = $request->ip();
        $location = 'Localhost';

        if ($ip !== '127.0.0.1' && $ip !== '::1') {
            try {
                $response = \Illuminate\Support\Facades\Http::get("http://ip-api.com/json/{$ip}?fields=status,city,regionName,country");
                if ($response->successful() && $response['status'] === 'success') {
                    $location = $response['city'] . ' / ' . $response['regionName'] . ' (' . $response['country'] . ')';
                }
            } catch (\Exception $e) {}
        }

        $token->forceFill([
            'device_uuid' => $uuid,
            'device_name' => $request->header('X-Device-Name') ?: 'Dispositivo Antigo',
            'device_type' => $request->header('X-Device-Type') ?: 'mobile',
            'ip_address'  => $ip,
            'user_agent'  => $request->userAgent(),
            'location'    => $location,
        ])->save();
    }
}

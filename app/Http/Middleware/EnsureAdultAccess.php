<?php

namespace App\Http\Middleware;

use App\Models\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdultAccess
{
    public function handle(Request $request, \Closure $next): Response
    {
        $config = AppConfig::getSettings();
        if (!$config->is_adult_active) {
            return response()->json([
                'message' => 'O modo adulto está desativado no momento.',
                'code' => 'ADULT_MODE_DISABLED',
            ], 403);
        }

        $profileId = $request->header('Profile-Id') ?: $request->header('X-Profile-Id');
        if (!$profileId) {
            return response()->json([
                'message' => 'Header Profile-Id é obrigatório para acessar o modo adulto.',
                'code' => 'PROFILE_REQUIRED',
            ], 400);
        }

        $profile = $request->user()?->profiles()->find($profileId);
        if (!$profile) {
            return response()->json(['message' => 'Perfil inválido para esta conta.'], 403);
        }

        if ($profile->is_kids || !$profile->is_adult_enabled) {
            return response()->json([
                'message' => 'O modo adulto não está autorizado para este perfil.',
                'code' => 'ADULT_NOT_ALLOWED',
            ], 403);
        }

        $tokenId = $request->user()?->currentAccessToken()?->id;
        $accessKey = $tokenId ? $this->accessKey($tokenId, $profile->id, $profile->adult_pin) : null;
        if (!$accessKey || !Cache::has($accessKey)) {
            return response()->json([
                'message' => 'Confirme o PIN adulto para continuar.',
                'code' => 'ADULT_PIN_REQUIRED',
            ], 403);
        }

        return $next($request);
    }

    public static function accessKey(int $tokenId, int $profileId, ?string $adultPin): string
    {
        return sprintf('adult_pin_access:%d:%d:%s', $tokenId, $profileId, sha1((string) $adultPin));
    }
}

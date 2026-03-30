<?php

namespace App\Http\Middleware;

use Closure;
use App\Contexts\ProfileContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SetActiveProfile
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Aceita tanto Profile-Id (padrão) quanto X-Profile-Id (Android legado ou específico)
        $profileId = $request->header('Profile-Id') ?: $request->header('X-Profile-Id');

        if ($profileId) {
            // Tenta obter o usuário do request primeiro
            $user = $request->user();

            // Se não houver usuário no request mas houver token de autorização, tenta autenticar via Sanctum
            if (!$user && $request->hasHeader('Authorization')) {
                $user = Auth::guard('sanctum')->user();
            }
            
            if ($user) {
                // Busca o perfil pertencente a este usuário
                $profile = $user->profiles()->find($profileId);
                if ($profile) {
                    ProfileContext::set($profile);
                }
            }
        }

        return $next($request);
    }
}

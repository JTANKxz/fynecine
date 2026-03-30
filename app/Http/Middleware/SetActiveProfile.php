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
        $profileId = $request->header('X-Profile-Id');

        if ($profileId) {
            // Se o request->user() for nulo, tentamos autenticar manualmente via Sanctum
            // Isso permite que o perfil seja identificado até em rotas públicas (como Home e Movies)
            $user = $request->user() ?: Auth::guard('sanctum')->user();
            
            if ($user) {
                $profile = $user->profiles()->find($profileId);
                if ($profile) {
                    ProfileContext::set($profile);
                }
            }
        }

        return $next($request);
    }
}

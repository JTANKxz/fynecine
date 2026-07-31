<?php

namespace App\Http\Middleware;

use App\Contexts\ProfileContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SetActiveProfile
{
    public function handle(Request $request, Closure $next)
    {
        $profileId = $request->header('Profile-Id') ?: $request->header('X-Profile-Id');

        // Authentication and profile-management routes must not be coupled to
        // a Profile-Id left over from a previous account in the browser.
        if (!$profileId || $request->is('api/auth/*') || $request->is('api/profiles*')) {
            return $next($request);
        }

        $user = $request->user();
        if (!$user && $request->hasHeader('Authorization')) {
            $user = Auth::guard('sanctum')->user();
        }

        if (!$user) {
            return $next($request);
        }

        $profile = $user->profiles()->find($profileId);
        if (!$profile) {
            return response()->json(['message' => 'Perfil invalido para esta conta.'], 403);
        }

        // Profile management and PIN verification must remain accessible even
        // when the profile currently stored in the browser is protected.
        if (!empty($profile->pin)) {
            $tokenId = $user->currentAccessToken()?->id;
            $isUnlocked = $tokenId && Cache::has("profile_pin_access:{$tokenId}:{$profile->id}");

            if (!$isUnlocked) {
                return response()->json([
                    'message' => 'Este perfil exige PIN antes de ser utilizado.',
                    'code' => 'PROFILE_PIN_REQUIRED',
                ], 403);
            }
        }

        ProfileContext::set($profile);

        return $next($request);
    }
}

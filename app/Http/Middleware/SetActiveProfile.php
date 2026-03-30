<?php

namespace App\Http\Middleware;

use Closure;
use App\Contexts\ProfileContext;
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
            $user = $request->user();
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

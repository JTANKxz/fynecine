<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoviePlayLink;
use App\Models\EpisodeLink;
use App\Services\BunnyLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LinkController extends Controller
{
    /**
     * Gera link assinado para filme.
     */
    public function moviePlay(Request $request, MoviePlayLink $link)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user && $link->player_sub !== 'free') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($link->player_sub === 'premium' && !$user->hasPlan()) {
            return response()->json(['error' => 'Premium required'], 403);
        }

        if ($link->type !== 'private') {
            return response()->json(['url' => $link->url]);
        }

        $signedUrl = BunnyLinkService::generateSignedUrl(
            $link->url, 
            $link->link_path, 
            $link->expiration_hours
        );

        return response()->json(['url' => $signedUrl]);
    }

    /**
     * Gera link assinado para episódio.
     */
    public function episodePlay(Request $request, EpisodeLink $link)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user && $link->player_sub !== 'free') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($link->player_sub === 'premium' && !$user->hasPlan()) {
            return response()->json(['error' => 'Premium required'], 403);
        }

        if ($link->type !== 'private') {
            return response()->json(['url' => $link->url]);
        }

        $signedUrl = BunnyLinkService::generateSignedUrl(
            $link->url, 
            $link->link_path, 
            $link->expiration_hours
        );

        return response()->json(['url' => $signedUrl]);
    }
}

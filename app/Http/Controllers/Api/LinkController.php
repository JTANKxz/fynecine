<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoviePlayLink;
use App\Models\EpisodeLink;
use App\Models\TvChannelLink;
use App\Models\EventLink;
use App\Models\MovieDownloadLink;
use App\Models\EpisodeDownloadLink;
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

        if ($link->player_sub === 'premium' && (!$user || !$user->hasPlan())) {
            return response()->json(['error' => 'Premium required'], 403);
        }

        $url = $link->url;
        if ($link->type === 'private' || $link->type === 'mp4') {
            $url = BunnyLinkService::generateSignedUrl(
                $link->url, 
                $link->link_path, 
                $link->expiration_hours
            );
        }

        $headers = [];
        if ($link->user_agent) $headers['User-Agent'] = $link->user_agent;
        if ($link->referer) $headers['Referer'] = $link->referer;
        if ($link->origin) $headers['Origin'] = $link->origin;

        return response()->json([
            'url'     => $url,
            'headers' => !empty($headers) ? $headers : null,
            'cookies' => $link->cookie
        ]);
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

        if ($link->player_sub === 'premium' && (!$user || !$user->hasPlan())) {
            return response()->json(['error' => 'Premium required'], 403);
        }

        $url = $link->url;
        if ($link->type === 'private' || $link->type === 'mp4') {
            $url = BunnyLinkService::generateSignedUrl(
                $link->url, 
                $link->link_path, 
                $link->expiration_hours
            );
        }

        $headers = [];
        if ($link->user_agent) $headers['User-Agent'] = $link->user_agent;
        if ($link->referer) $headers['Referer'] = $link->referer;
        if ($link->origin) $headers['Origin'] = $link->origin;

        return response()->json([
            'url'     => $url,
            'headers' => !empty($headers) ? $headers : null,
            'cookies' => $link->cookie
        ]);
    }

    /**
     * Gera link assinado para canal de TV.
     */
    public function channelPlay(Request $request, TvChannelLink $link)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user && $link->player_sub !== 'free') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($link->player_sub === 'premium' && (!$user || !$user->hasPlan())) {
            return response()->json(['error' => 'Premium required'], 403);
        }

        $url = $link->url;
        if ($link->type === 'private' || $link->type === 'mp4') {
            $url = BunnyLinkService::generateSignedUrl(
                $link->url, 
                $link->link_path, 
                $link->expiration_hours
            );
        }

        $headers = [];
        if ($link->user_agent) $headers['User-Agent'] = $link->user_agent;
        if ($link->referer) $headers['Referer'] = $link->referer;
        if ($link->origin) $headers['Origin'] = $link->origin;

        return response()->json([
            'url'     => $url,
            'headers' => !empty($headers) ? $headers : null,
            'cookies' => $link->cookie
        ]);
    }

    /**
     * Gera link assinado para evento ao vivo.
     */
    public function eventPlay(Request $request, EventLink $link)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user && $link->player_sub !== 'free') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($link->player_sub === 'premium' && (!$user || !$user->hasPlan())) {
            return response()->json(['error' => 'Premium required'], 403);
        }

        $url = $link->url;
        if ($link->type === 'private') {
            $url = BunnyLinkService::generateSignedUrl(
                $link->url, 
                $link->link_path, 
                $link->expiration_hours
            );
        }

        $headers = [];
        if ($link->user_agent) $headers['User-Agent'] = $link->user_agent;
        if ($link->referer) $headers['Referer'] = $link->referer;
        if ($link->origin) $headers['Origin'] = $link->origin;

        return response()->json([
            'url'     => $url,
            'headers' => !empty($headers) ? $headers : null,
            'cookies' => $link->cookie
        ]);
    /**
     * Gera link assinado para download de filme.
     */
    public function movieDownload(Request $request, MovieDownloadLink $link)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user && $link->download_sub !== 'free') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($link->download_sub === 'premium' && (!$user || !$user->hasPlan())) {
            return response()->json(['error' => 'Premium required'], 403);
        }

        $url = $link->url;
        if ($link->type === 'private' || $link->type === 'mp4') {
            $url = BunnyLinkService::generateSignedUrl(
                $link->url, 
                $link->link_path, 
                $link->expiration_hours
            );
        }

        return response()->json([
            'url'     => $url,
            'headers' => null,
            'cookies' => null
        ]);
    }

    /**
     * Gera link assinado para download de episódio.
     */
    public function episodeDownload(Request $request, EpisodeDownloadLink $link)
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user && $link->download_sub !== 'free') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($link->download_sub === 'premium' && (!$user || !$user->hasPlan())) {
            return response()->json(['error' => 'Premium required'], 403);
        }

        $url = $link->url;
        if ($link->type === 'private' || $link->type === 'mp4') {
            $url = BunnyLinkService::generateSignedUrl(
                $link->url, 
                $link->link_path, 
                $link->expiration_hours
            );
        }

        return response()->json([
            'url'     => $url,
            'headers' => null,
            'cookies' => null
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TvChannel;
use App\Models\TvChannelCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TvChannelController extends Controller
{

    public function index(Request $request)
    {
        $query = TvChannel::with('categories');

        // Filtro por categoria
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Busca por nome
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $channels = $query->orderBy('name')->paginate(20);

        return response()->json($channels);
    }

    public function show($idOrSlug)
    {
        $channel = TvChannel::with(['categories', 'links'])
            ->where(function ($query) use ($idOrSlug) {
                if (is_numeric($idOrSlug)) {
                    $query->where('id', $idOrSlug);
                } else {
                    $query->where('slug', $idOrSlug);
                }
            })
            ->firstOrFail();

        $config = \App\Models\AppConfig::getSettings();

        $playLinks = collect();

        if (!$config->security_mode) {
            $user = Auth::guard('sanctum')->user();
            $hasPlan = $user && $user->hasPlan();

            foreach ($channel->links as $link) {
                $url = ($hasPlan || $link->player_sub === 'free') ? $link->url : null;
                
                if ($url && ($link->type === 'private' || $link->type === 'mp4')) {
                    $url = url("/api/links/channel/{$link->id}/play");
                }

                $playLinks->push([
                    'id'   => $link->id,
                    'name' => $link->name,
                    'url'  => $url,
                    'type' => $link->type,
                    'headers' => [
                        'user_agent' => $link->user_agent,
                        'referer' => $link->referer,
                        'origin' => $link->origin,
                        'cookie' => $link->cookie,
                    ]
                ]);
            }
        }

        return response()->json([
            'id'         => $channel->id,
            'name'       => $channel->name,
            'slug'       => $channel->slug,
            'image_url'  => $channel->image_url,

            'categories' => $channel->categories->map(function ($cat) {
                return [
                    'id'   => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ];
            }),

            'play_links' => $playLinks->values(),
        ]);
    }

    public function categories()
    {
        $categories = TvChannelCategory::withCount('channels')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }
}

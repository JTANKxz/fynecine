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

        $perPage = min(100, max(1, (int) $request->input('per_page', 100)));
        $channels = $query->orderBy('name')->paginate($perPage)->withQueryString();

        $ids = $channels->getCollection()->pluck('id');
        $now = now();
        $current = \App\Models\EpgProgram::whereIn('tv_channel_id', $ids)->where('starts_at', '<=', $now)->where('ends_at', '>', $now)->get()->keyBy('tv_channel_id');
        $next = \App\Models\EpgProgram::whereIn('tv_channel_id', $ids)->where('starts_at', '>=', $now)->orderBy('starts_at')->get()->groupBy('tv_channel_id')->map->first();
        $channels->getCollection()->transform(function ($channel) use ($current, $next) {
            $channel->now_playing = $current->get($channel->id);
            $channel->next_program = $next->get($channel->id);
            return $channel;
        });
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
            'schedule' => \App\Models\EpgProgram::where('tv_channel_id', $channel->id)->where('ends_at', '>', now()->subHour())->orderBy('starts_at')->limit(24)->get(),
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

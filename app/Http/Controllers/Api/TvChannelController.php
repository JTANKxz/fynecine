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
            // Se o usuário está logado e tem plano, mostra todos os links
            if (Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user()->hasPlan()) {

                $playLinks = $channel->links->map(function ($link) {
                    return [
                        'id'   => $link->id,
                        'name' => $link->name,
                        'url'  => $link->url,
                        'type' => $link->type,
                    ];
                });

            } else {
                // Se não tem plano, mostra apenas links free
                $playLinks = $channel->links
                    ->where('player_sub', 'free')
                    ->values()
                    ->map(function ($link) {
                        return [
                            'id'   => $link->id,
                            'name' => $link->name,
                            'url'  => $link->url,
                            'type' => $link->type,
                        ];
                    });
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

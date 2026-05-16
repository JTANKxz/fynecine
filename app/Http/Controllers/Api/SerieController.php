<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SerieController extends Controller
{
    public function index(Request $request)
    {
        $query = Serie::with('genres');

        /*
        =========================
        BUSCA
        =========================
        */

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        /*
        =========================
        FILTRO POR GENERO
        =========================
        */

        if ($request->filled('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('slug', $request->genre);
            });
        }

        /*
        =========================
        FILTRO POR CATEGORIA
        =========================
        */

        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        } else {
            $query->whereNull('content_category_id');
        }

        /*
        =========================
        ORDENAÇÃO E FILTROS
        =========================
        */

        if ($request->filled('year')) {
            $query->where('first_air_year', $request->year);
        }

        $order = $request->get('order', 'desc');
        if (!in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'rating':
                    $query->orderBy('rating', $order);
                    break;
                case 'year':
                    $query->orderBy('first_air_year', $order);
                    break;
                case 'title':
                    $query->orderBy('name', $order);
                    break;
                default:
                    $query->orderBy('id', $order);
            }
        } else {
            $query->orderBy('id', $order);
        }

        /*
        =========================
        PAGINAÇÃO
        =========================
        */

        $series = $query->paginate(20);

        return response()->json([
            'data' => $series->map(function ($serie) {

                return [
                    'id' => $serie->id,
                    'type' => 'series',
                    'name' => $serie->name,
                    'slug' => $serie->slug,
                    'year' => $serie->first_air_year,
                    'rating' => $serie->rating,
                    'seasons' => $serie->number_of_seasons,
                    'episodes' => $serie->number_of_episodes,
                    'poster' => $serie->poster_path,
                    'backdrop' => $serie->backdrop_path,
                    'tag_text' => $serie->api_tag_text,
                ];

            }),

            'current_page' => $series->currentPage(),
            'last_page' => $series->lastPage(),
            'per_page' => $series->perPage(),
            'total' => $series->total()
        ]);
    }

    public function show($idOrSlug)
    {

        $serie = Serie::with([
            'genres',
            'seasons.episodes.links',
            'seasons.episodes.downloadLinks',
            'cast' => function ($q) {
                $q->orderBy('pivot_order');
            }
        ])
            ->where(function ($query) use ($idOrSlug) {

                if (is_numeric($idOrSlug)) {
                    $query->where('id', $idOrSlug);
                } else {
                    $query->where('slug', $idOrSlug);
                }

            })
            ->firstOrFail();


        /*
        =========================
        RELACIONADOS
        =========================
        */

        $related = Serie::whereHas('genres', function ($q) use ($serie) {
            $q->whereIn('genres.id', $serie->genres->pluck('id'));
        })
            ->where('id', '!=', $serie->id)
            ->limit(12)
            ->get();

        $config = \App\Models\AppConfig::getSettings();

        return response()->json([

            'id' => $serie->id,
            'tmdb_id' => $serie->tmdb_id,
            'name' => $serie->name,
            'slug' => $serie->slug,
            'year' => $serie->first_air_year,
            'last_year' => $serie->last_air_year,
            'rating' => $serie->rating,
            'overview' => $serie->overview,

            'poster' => $serie->poster_path,
            'backdrop' => $serie->backdrop_path,
            'tag_text' => $serie->api_tag_text,

            'trailer' => [
                'key' => $serie->trailer_key,
                'url' => $serie->trailer_url,
                'embed' => $serie->trailer_key
                    ? "https://www.youtube.com/embed/" . $serie->trailer_key
                    : null
            ],

            'genres' => $serie->genres->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'slug' => $genre->slug
                ];
            }),


            'cast' => $serie->cast->map(function ($actor) {
                return [
                    'id' => $actor->id,
                    'name' => $actor->name,
                    'slug' => $actor->slug,
                    'profile' => $actor->profile_path,

                    'character' => $actor->pivot->character,
                    'order' => $actor->pivot->order
                ];
            }),

            'seasons' => $serie->seasons->map(function ($season) use ($serie, $config) {

                return [
                    'id' => $season->id,
                    'season_number' => $season->season_number,

                    'episodes' => $season->episodes->map(function ($episode) use ($serie, $season, $config) {

                        $links = collect();
                        $embedUrl = null;
                        $downloadLinks = collect();

                        if (!$config->security_mode) {
                            $user = Auth::guard('sanctum')->user();
                            $hasPlan = $user && $user->hasPlan();

                            $links = $episode->links->sortBy('order')->map(function($link) use ($hasPlan) {
                                $url = ($hasPlan || $link->player_sub === 'free') ? $link->url : null;
                                if ($url && ($link->type === 'private' || $link->type === 'mp4')) {
                                    $url = url("/api/links/episode/{$link->id}/play");
                                }
                                return [
                                    'id' => $link->id,
                                    'name' => $link->name,
                                    'url' => $url,
                                    'type' => $link->type,
                                    'quality' => $link->quality,
                                    'player_sub' => $link->player_sub,
                                    'skip_intro_start' => $link->skip_intro_start,
                                    'skip_intro_end' => $link->skip_intro_end,
                                    'skip_ending_start' => $link->skip_ending_start,
                                    'skip_ending_end' => $link->skip_ending_end,
                                    'skip_recap_start' => $link->skip_recap_start,
                                    'skip_recap_end' => $link->skip_recap_end,
                                    'headers' => [
                                        'user_agent' => $link->user_agent,
                                        'referer' => $link->referer,
                                        'origin' => $link->origin,
                                        'cookie' => $link->cookie,
                                    ]
                                ];
                            });

                            if ($config->autoembed_series && $serie->use_autoembed) {
                                $excluded = $serie->excluded_autoembeds ?? [];
                                
                                $autoSources = collect($config->autoembed_serie_sources ?? [])->map(function($s) use ($serie, $season, $episode) {
                                    $autoSub = $s['player_sub'] ?? 'free';
                                    return [
                                        'id' => \Illuminate\Support\Str::slug($s['name'] ?? 'player'),
                                        'name' => $s['name'] ?? 'Auto Player',
                                        'url' => str_replace(
                                            ['{tmdb_id}', '{season}', '{episode}'],
                                            [$serie->tmdb_id, $season->season_number, $episode->episode_number],
                                            $s['url'] ?? ''
                                        ),
                                        'type' => $s['type'] ?? 'embed',
                                        'quality' => $s['quality'] ?? 'HD',
                                        'player_sub' => $autoSub,
                                        'headers' => [
                                            'user_agent' => $s['user_agent'] ?? null,
                                            'referer' => $s['referer'] ?? null,
                                            'origin' => $s['origin'] ?? null,
                                            'cookie' => $s['cookie'] ?? null,
                                        ]
                                    ];
                                });

                                foreach ($autoSources as $autoSource) {
                                    // Filter if ID is in excluded list
                                    if (!in_array($autoSource['id'], $excluded)) {
                                        $url = ($hasPlan || $autoSource['player_sub'] === 'free') ? $autoSource['url'] : null;
                                        $autoSource['url'] = $url;
                                        $links->push($autoSource);
                                    }
                                }

                                // Also set embedUrl for legacy compatibility if needed
                                $firstLink = $links->where('id', 'auto')->first() ?? $links->last();
                                if ($firstLink) $embedUrl = $firstLink['url'];
                            }

                            // Download links do episódio filtrados por plano
                            foreach ($episode->downloadLinks as $dl) {
                                $url = ($hasPlan || $dl->download_sub === 'free') ? $dl->url : null;
                                if ($url && ($dl->type === 'private' || $dl->type === 'mp4')) {
                                    $url = url("/api/links/episode/download/{$dl->id}");
                                }
                                $downloadLinks->push([
                                    'id'           => $dl->id,
                                    'name'         => $dl->name,
                                    'url'          => $url,
                                    'quality'      => $dl->quality,
                                    'size'         => $dl->size,
                                    'type'         => $dl->type,
                                    'download_sub' => $dl->download_sub,
                                ]);
                            }
                        }

                        return [
                            'id' => $episode->id,
                            'episode_number' => $episode->episode_number,
                            'name' => $episode->name,
                            'overview' => $episode->overview,
                            'duration' => $episode->duration,
                            'still' => $episode->still_path,

                            // 🔥 AUTO EMBED
                            'embed' => $embedUrl,

                            'links'          => $links->values(),
                            'download_links' => $downloadLinks->values(),
                        ];

                    })

                ];

            }),

            /*
            =========================
            RELACIONADOS
            =========================
            */

            'related' => $related

        ]);
    }
}
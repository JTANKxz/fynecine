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
        ORDENAÇÃO
        =========================
        */

        if ($request->filled('sort')) {

            switch ($request->sort) {

                case 'rating':
                    $query->orderBy('rating', 'desc');
                    break;

                case 'year':
                    $query->orderBy('first_air_year', 'desc');
                    break;

                default:
                    $query->latest();
            }

        } else {
            $query->latest();
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
                    'name' => $serie->name,
                    'slug' => $serie->slug,
                    'year' => $serie->first_air_year,
                    'rating' => $serie->rating,
                    'seasons' => $serie->number_of_seasons,
                    'episodes' => $serie->number_of_episodes,
                    'poster' => $serie->poster_path,
                    'backdrop' => $serie->backdrop_path
                ];

            }),

            'pagination' => [
                'current_page' => $series->currentPage(),
                'last_page' => $series->lastPage(),
                'per_page' => $series->perPage(),
                'total' => $series->total()
            ]
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

                            foreach ($episode->links as $link) {
                                $links->push([
                                    'id' => $link->id,
                                    'name' => $link->name,
                                    'url' => $hasPlan || $link->player_sub === 'free' ? $link->url : null,
                                    'type' => $link->type,
                                    'quality' => $link->quality,
                                    'player_sub' => $link->player_sub,
                                    'skip_intro_start' => $link->skip_intro_start,
                                    'skip_intro_end' => $link->skip_intro_end,
                                    'skip_ending_start' => $link->skip_ending_start,
                                    'skip_ending_end' => $link->skip_ending_end,
                                ]);
                            }

                            if ($config->autoembed_series && $config->autoembed_serie_url) {
                                $autoSub = $config->autoembed_serie_player_sub ?? 'free';
                                $embedUrl = str_replace(
                                    ['{tmdb_id}', '{season}', '{episode}'],
                                    [$serie->tmdb_id, $season->season_number, $episode->episode_number],
                                    $config->autoembed_serie_url
                                );
                                
                                $links->push([
                                    'id' => 'auto',
                                    'name' => $config->autoembed_serie_name ?? 'Auto Player',
                                    'url' => $hasPlan || $autoSub === 'free' ? $embedUrl : null,
                                    'type' => $config->autoembed_serie_type ?? 'embed',
                                    'quality' => $config->autoembed_serie_quality ?? 'HD',
                                    'player_sub' => $autoSub
                                ]);
                            }

                            // Download links do episódio filtrados por plano
                            foreach ($episode->downloadLinks as $dl) {
                                $downloadLinks->push([
                                    'id'           => $dl->id,
                                    'name'         => $dl->name,
                                    'url'          => $hasPlan || $dl->download_sub === 'free' ? $dl->url : null,
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
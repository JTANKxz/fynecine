<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{

    public function index(Request $request)
    {
        $query = Movie::with('genres');

        /*
        =========================
        BUSCA
        =========================
        */

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
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
                    $query->orderBy('release_year', 'desc');
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

        $movies = $query->paginate(20);

        return response()->json($movies);
    }

    public function show($idOrSlug)
    {

        $movie = Movie::with([
            'genres',
            'playLinks',
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
        FILMES RELACIONADOS
        =========================
        */

        $related = Movie::whereHas('genres', function ($q) use ($movie) {
            $q->whereIn('genres.id', $movie->genres->pluck('id'));
        })
            ->where('id', '!=', $movie->id)
            ->limit(12)
            ->get();

        $config = \App\Models\AppConfig::getSettings();
        
        $playLinks = collect();
        if (!$config->security_mode) {
            if (Auth::guard('sanctum')->check() && Auth::guard('sanctum')->user()->hasPlan()) {
                
                $playLinks = $movie->playLinks->map(function ($link) {
                    return [
                        'id' => $link->id,
                        'name' => $link->name,
                        'url' => $link->url,
                        'type' => $link->type
                    ];
                });

                if ($config->autoembed_movies && $config->autoembed_movie_url) {
                    $url = str_replace('{tmdb_id}', $movie->tmdb_id, $config->autoembed_movie_url);
                    $playLinks->push([
                        'id' => 'auto', 
                        'name' => 'Auto Player', 
                        'url' => $url, 
                        'type' => 'embed'
                    ]);
                }
            }
        }

        return response()->json([

            'id' => $movie->id,
            'title' => $movie->title,
            'slug' => $movie->slug,
            'year' => $movie->release_year,
            'runtime' => $movie->runtime,
            'rating' => $movie->rating,
            'overview' => $movie->overview,

            'poster' => $movie->poster_path,
            'backdrop' => $movie->backdrop_path,

            'trailer' => [
                'key' => $movie->trailer_key,
                'url' => $movie->trailer_url,
                'embed' => $movie->trailer_embed
            ],

            'genres' => $movie->genres->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'slug' => $genre->slug
                ];
            }),

            'cast' => $movie->cast->map(function ($actor) {
                return [
                    'id' => $actor->id,
                    'name' => $actor->name,
                    'slug' => $actor->slug,
                    'profile' => $actor->profile_path,

                    'character' => $actor->pivot->character,
                    'order' => $actor->pivot->order
                ];
            }),

            /*
            =========================
            PROTEÇÃO COM PLANO / LINKS
            =========================
            */
            'play_links' => $playLinks->values(),

            'related' => $related

        ]);
    }
}
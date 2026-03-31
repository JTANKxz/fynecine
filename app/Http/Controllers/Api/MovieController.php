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
            'downloadLinks',
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
        
        $user = Auth::guard('sanctum')->user();
        $hasPlan = $user && $user->hasPlan();

        $playLinks = collect();
        if (!$config->security_mode) {
            foreach ($movie->playLinks as $link) {
                $playLinks->push([
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

            if ($config->autoembed_movies && $config->autoembed_movie_url) {
                $autoSub = $config->autoembed_movie_player_sub ?? 'free';
                $url = str_replace('{tmdb_id}', $movie->tmdb_id, $config->autoembed_movie_url);
                $playLinks->push([
                    'id' => 'auto', 
                    'name' => $config->autoembed_movie_name ?? 'Auto Player', 
                    'url' => $hasPlan || $autoSub === 'free' ? $url : null, 
                    'type' => $config->autoembed_movie_type ?? 'embed',
                    'quality' => $config->autoembed_movie_quality ?? 'HD',
                    'player_sub' => $autoSub
                ]);
            }
        }

        // Download links filtrados por plano
        $downloadLinks = collect();
        if (!$config->security_mode) {
            foreach ($movie->downloadLinks as $dl) {
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

        return response()->json([

            'id' => $movie->id,
            'tmdb_id' => $movie->tmdb_id,
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
            'play_links'     => $playLinks->values(),
            'download_links' => $downloadLinks->values(),

            'related' => $related

        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cast;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\Request;

class CastController extends Controller
{
    public function show($idOrSlug, Request $request)
    {
        if (is_numeric($idOrSlug)) {
            $cast = Cast::findOrFail($idOrSlug);
        } else {
            $cast = Cast::where('slug', $idOrSlug)->firstOrFail();
        }

        /*
        =========================
        FILMES DO ATOR
        =========================
        */

        $movies = Movie::whereHas('cast', function ($q) use ($cast) {
            $q->where('casts.id', $cast->id);
        })
        ->get()
        ->map(function ($movie) {
            return [
                'id' => $movie->id,
                'slug' => $movie->slug,
                'title' => $movie->title,
                'type' => 'movie',
                'rating' => $movie->rating,
                'year' => $movie->release_year,
                'poster' => $movie->poster_path,
                'backdrop' => $movie->backdrop_path,
                'tag_text' => $movie->api_tag_text,
            ];
        });

        /*
        =========================
        SERIES DO ATOR
        =========================
        */

        $series = Serie::whereHas('cast', function ($q) use ($cast) {
            $q->where('casts.id', $cast->id);
        })
        ->get()
        ->map(function ($serie) {
            return [
                'id' => $serie->id,
                'slug' => $serie->slug,
                'title' => $serie->name,
                'type' => 'series',
                'rating' => $serie->rating,
                'year' => $serie->first_air_year,
                'poster' => $serie->poster_path,
                'backdrop' => $serie->backdrop_path,
                'tag_text' => $serie->api_tag_text,
            ];
        });

        /*
        =========================
        JUNTA TUDO
        =========================
        */

        $content = $movies->concat($series)->sortByDesc('rating')->values();

        /*
        =========================
        PAGINAÇÃO MANUAL
        =========================
        */

        $page = (int) $request->get('page', 1);
        $perPage = 20;

        $paginated = $content->slice(($page - 1) * $perPage, $perPage)->values();

        return response()->json([
            'cast' => [
                'id' => $cast->id,
                'name' => $cast->name,
                'slug' => $cast->slug,
                'profile' => $cast->profile_path,
                'biography' => $cast->biography,
                'birthday' => $cast->birthday,
            ],

            'data' => $paginated,

            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $content->count(),
            'last_page' => (int) ceil($content->count() / $perPage)
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function show($idOrSlug, Request $request)
    {
        if (is_numeric($idOrSlug)) {
            $genre = Genre::findOrFail($idOrSlug);
        } else {
            $genre = Genre::where('slug', $idOrSlug)->firstOrFail();
        }

        /*
        =========================
        FILMES DO GENERO
        =========================
        */

        $movies = Movie::whereHas('genres', function ($q) use ($genre) {
            $q->where('genres.id', $genre->id);
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
                'backdrop' => $movie->backdrop_path
            ];
        });

        /*
        =========================
        SERIES DO GENERO
        =========================
        */

        $series = Serie::whereHas('genres', function ($q) use ($genre) {
            // 🔥 CORRIGIDO AQUI
            $q->where('genres.id', $genre->id);
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
                'backdrop' => $serie->backdrop_path
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
            'genre' => [
                'id' => $genre->id,
                'name' => $genre->name,
                'slug' => $genre->slug
            ],

            'data' => $paginated,

            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $content->count(),
                'last_page' => (int) ceil($content->count() / $perPage)
            ]
        ]);
    }
}
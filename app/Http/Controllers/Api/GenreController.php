<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\Request;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::select('id', 'name', 'slug')->get();
        return response()->json($genres);
    }

    public function show($idOrSlug, Request $request)
    {
        if (is_numeric($idOrSlug)) {
            $genre = Genre::findOrFail($idOrSlug);
        } else {
            $genre = Genre::where('slug', $idOrSlug)->firstOrFail();
        }

        $order = $request->get('order', 'desc');
        if (!in_array($order, ['asc', 'desc'])) { $order = 'desc'; }
        
        $sort = $request->get('sort', 'rating');
        $year = $request->get('year');

        /*
        =========================
        FILMES DO GENERO
        =========================
        */

        $movieQuery = Movie::whereHas('genres', function ($q) use ($genre) {
            $q->where('genres.id', $genre->id);
        });

        if ($year) {
            $movieQuery->where('release_year', $year);
        }

        $movies = $movieQuery->get()->map(function ($movie) {
            return [
                'id' => $movie->id,
                'slug' => $movie->slug,
                'title' => $movie->title,
                'type' => 'movie',
                'rating' => $movie->rating,
                'year' => $movie->release_year,
                'poster' => $movie->poster_path,
                'backdrop' => $movie->backdrop_path,
                'created_at' => $movie->created_at
            ];
        });

        /*
        =========================
        SERIES DO GENERO
        =========================
        */

        $serieQuery = Serie::whereHas('genres', function ($q) use ($genre) {
            $q->where('genres.id', $genre->id);
        });

        if ($year) {
            $serieQuery->where('first_air_year', $year);
        }

        $series = $serieQuery->get()->map(function ($serie) {
            return [
                'id' => $serie->id,
                'slug' => $serie->slug,
                'title' => $serie->name,
                'type' => 'series',
                'rating' => $serie->rating,
                'year' => $serie->first_air_year,
                'poster' => $serie->poster_path,
                'backdrop' => $serie->backdrop_path,
                'created_at' => $serie->created_at
            ];
        });

        /*
        =========================
        JUNTA E ORDENA
        =========================
        */

        $content = $movies->concat($series);
        
        switch ($sort) {
            case 'year':
                $content = ($order === 'asc') ? $content->sortBy('year') : $content->sortByDesc('year');
                break;
            case 'title':
                $content = ($order === 'asc') ? $content->sortBy('title') : $content->sortByDesc('title');
                break;
            case 'rating':
            default:
                $content = ($order === 'asc') ? $content->sortBy('rating') : $content->sortByDesc('rating');
                break;
        }

        $content = $content->values();

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

            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $content->count(),
            'last_page' => (int) ceil($content->count() / $perPage)
        ]);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q');

        if (!$query) {
            return response()->json([
                'data' => []
            ]);
        }

        /*
        =========================
        FILMES
        =========================
        */

        $movies = Movie::where('title', 'like', "%{$query}%")
            ->limit(20)
            ->get()
            ->map(function ($movie) {
                return [
                    'id' => $movie->id,
                    'slug' => $movie->slug,
                    'title' => $movie->title,
                    'type' => 'movie',
                    'year' => $movie->release_year,
                    'rating' => $movie->rating,
                    'poster' => $movie->poster_path,
                    'backdrop' => $movie->backdrop_path,
                    'tag_text' => $movie->api_tag_text,
                ];
            });

        /*
        =========================
        SERIES
        =========================
        */

        $series = Serie::where('name', 'like', "%{$query}%")
            ->limit(20)
            ->get()
            ->map(function ($serie) {
                return [
                    'id' => $serie->id,
                    'slug' => $serie->slug,
                    'title' => $serie->name,
                    'type' => 'series',
                    'year' => $serie->first_air_year,
                    'rating' => $serie->rating,
                    'poster' => $serie->poster_path,
                    'backdrop' => $serie->backdrop_path,
                    'tag_text' => $serie->api_tag_text,
                ];
            });

        /*
        =========================
        JUNTA RESULTADOS
        =========================
        */

        $results = $movies
            ->concat($series)
            ->sortByDesc('rating')
            ->values();

        return response()->json([
            'query' => $query,
            'data' => $results
        ]);
    }
}
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
    public function suggestions(Request $request)
    {
        $limit = 12;

        /*
        =========================
        MAIS VISTOS DA SEMANA (Geral)
        =========================
        */
        $trendingWeek = \App\Models\ContentView::select('content_id', 'content_type')
            ->selectRaw('COUNT(*) as views_count')
            ->where('viewed_at', '>=', now()->subWeek())
            ->groupBy('content_id', 'content_type')
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $content = $item->content_type === 'movie' 
                    ? Movie::find($item->content_id) 
                    : Serie::find($item->content_id);
                
                return $content ? $this->formatItem($content) : null;
            })->filter()->values();

        /*
        =========================
        MAIS VISTOS - FILMES
        =========================
        */
        $trendingMovies = \App\Models\ContentView::select('content_id')
            ->selectRaw('COUNT(*) as views_count')
            ->where('content_type', 'movie')
            ->groupBy('content_id')
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $movie = Movie::find($item->content_id);
                return $movie ? $this->formatItem($movie) : null;
            })->filter()->values();

        /*
        =========================
        MAIS VISTOS - SERIES
        =========================
        */
        $trendingSeries = \App\Models\ContentView::select('content_id')
            ->selectRaw('COUNT(*) as views_count')
            ->where('content_type', 'series')
            ->groupBy('content_id')
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $serie = Serie::find($item->content_id);
                return $serie ? $this->formatItem($serie) : null;
            })->filter()->values();

        return response()->json([
            [
                'title' => 'Mais Vistos da Semana',
                'items' => $trendingWeek
            ],
            [
                'title' => 'Filmes em Alta',
                'items' => $trendingMovies
            ],
            [
                'title' => 'Séries em Alta',
                'items' => $trendingSeries
            ]
        ]);
    }

    private function formatItem($item)
    {
        $isMovie = $item instanceof Movie;
        return [
            'id' => $item->id,
            'slug' => $item->slug,
            'title' => $isMovie ? $item->title : $item->name,
            'type' => $isMovie ? 'movie' : 'series',
            'year' => $isMovie ? $item->release_year : $item->first_air_year,
            'rating' => $item->rating,
            'poster' => $item->poster_path,
            'backdrop' => $item->backdrop_path,
            'tag_text' => $item->api_tag_text,
            'age_rating' => $item->age_rating,
        ];
    }
}
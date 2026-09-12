<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Serie;
use App\Models\AppConfig;
use App\Models\Genre;
use App\Models\HomeSection;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q');
        $genreSlug = $request->get('genre');
        // Trata hífens e pontuação como separadores. Assim "homem aranha"
        // encontra "Homem-Aranha", sem depender da grafia exata do catálogo.
        $terms = collect(preg_split('/[\s\p{P}]+/u', (string) $query, -1, PREG_SPLIT_NO_EMPTY))
            ->filter(fn ($term) => mb_strlen($term) >= 2)
            ->values();

        if (!$query && !$genreSlug) {
            return response()->json([
                'data' => []
            ]);
        }

        $genre = null;
        if ($genreSlug) {
            $genre = Genre::where('slug', $genreSlug)->first();
        } elseif ($query) {
            $genre = Genre::where('name', 'like', $query)->orWhere('slug', 'like', $query)->first();
        }
        $isGenreSearch = $genre && !$genreSlug && (
            strcasecmp(trim((string) $query), $genre->name) === 0 ||
            strcasecmp(trim((string) $query), $genre->slug) === 0
        );

        /*
        =========================
        FILMES
        =========================
        */

        $movieQuery = Movie::query();
        if ($query && !$isGenreSearch) {
            $movieQuery->where(function ($q) use ($terms, $query) {
                $q->where(function ($title) use ($terms, $query) {
                    foreach ($terms->isNotEmpty() ? $terms : collect([$query]) as $term) {
                        $title->where('title', 'like', "%{$term}%");
                    }
                })->orWhereHas('cast', function ($cast) use ($terms, $query) {
                    foreach ($terms->isNotEmpty() ? $terms : collect([$query]) as $term) {
                        $cast->where('name', 'like', "%{$term}%");
                    }
                });
            });
        }
        if ($genre) {
            $movieQuery->whereHas('genres', function ($q) use ($genre) {
                $q->where('genres.id', $genre->id);
            });
        }

        $movies = $movieQuery->limit(100)
            ->get()
            ->map(function ($movie) {
                return [
                    'id' => $movie->id,
                    'slug' => $movie->slug,
                    'title' => $movie->title,
                    'type' => 'movie',
                    'year' => $movie->release_year,
                    'rating' => $movie->rating,
                    'runtime' => $movie->runtime,
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

        $serieQuery = Serie::query();
        if ($query && !$isGenreSearch) {
            $serieQuery->where(function ($q) use ($terms, $query) {
                $q->where(function ($name) use ($terms, $query) {
                    foreach ($terms->isNotEmpty() ? $terms : collect([$query]) as $term) {
                        $name->where('name', 'like', "%{$term}%");
                    }
                })->orWhereHas('cast', function ($cast) use ($terms, $query) {
                    foreach ($terms->isNotEmpty() ? $terms : collect([$query]) as $term) {
                        $cast->where('name', 'like', "%{$term}%");
                    }
                });
            });
        }
        if ($genre) {
            $serieQuery->whereHas('genres', function ($q) use ($genre) {
                $q->where('genres.id', $genre->id);
            });
        }

        $series = $serieQuery->limit(100)
            ->get()
            ->map(function ($serie) {
                return [
                    'id' => $serie->id,
                    'slug' => $serie->slug,
                    'title' => $serie->name,
                    'type' => 'series',
                    'year' => $serie->first_air_year,
                    'rating' => $serie->rating,
                    'seasons' => $serie->number_of_seasons,
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

        $perPage = 20;
        $page = max(1, (int) $request->integer('page', 1));
        $total = $results->count();
        $lastPage = max(1, (int) ceil($total / $perPage));

        return response()->json([
            'query' => $query,
            'data' => $results->slice(($page - 1) * $perPage, $perPage)->values(),
            'current_page' => $page,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
        ]);
    }

    public function discover()
    {
        $config = AppConfig::getSettings();
        $genreIds = array_values(array_filter($config->search_genre_ids ?? []));
        $genres = Genre::query()
            ->when($genreIds, fn ($q) => $q->whereIn('id', $genreIds))
            ->orderByRaw($genreIds ? 'FIELD(id, '.implode(',', array_map('intval', $genreIds)).')' : 'name')
            ->limit(12)
            ->get(['id', 'name', 'slug']);

        $collectionIds = array_values(array_filter($config->search_collection_ids ?? []));
        $collections = $collectionIds
            ? HomeSection::query()->where('is_active', true)
                ->whereIn('id', $collectionIds)
                ->orderByRaw('FIELD(id, '.implode(',', array_map('intval', $collectionIds)).')')
                ->get(['id', 'title', 'slug'])
            : collect();

        return response()->json([
            'genres' => $genres,
            'collections' => $collections->map(fn ($section) => [
                'id' => $section->id,
                'title' => $section->title,
                'slug' => $section->slug,
                'type' => 'section',
            ])->values(),
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

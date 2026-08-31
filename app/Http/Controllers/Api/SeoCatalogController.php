<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Serie;
use App\Models\HomeSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeoCatalogController extends Controller
{
    public function show(string $type, string $idOrSlug): JsonResponse
    {
        $isMovie = $type === 'movie';
        $model = $isMovie ? Movie::class : Serie::class;
        $item = $model::query()
            ->with([
                'genres:id,name,slug',
                'keywords:id,name',
                'cast' => fn ($query) => $query
                    ->select(['casts.id', 'name', 'slug', 'profile_path'])
                    ->orderBy('pivot_order'),
                ...($isMovie ? [] : [
                    'seasons' => fn ($query) => $query
                        ->select(['id', 'series_id', 'season_number'])
                        ->withCount('episodes')
                        ->orderBy('season_number'),
                ]),
            ])
            ->where(is_numeric($idOrSlug) ? 'id' : 'slug', $idOrSlug)
            ->firstOrFail();

        return response()->json([
            'id' => $item->id,
            'slug' => $item->slug,
            'title' => $isMovie ? $item->title : $item->name,
            'overview' => $item->overview,
            'poster' => $item->poster_path,
            'backdrop' => $item->backdrop_path,
            'logo' => $item->logo_path,
            'year' => $isMovie ? $item->release_year : $item->first_air_year,
            'rating' => $item->rating,
            'vote_count' => $item->vote_count,
            'age_rating' => $item->age_rating,
            'runtime' => $isMovie ? $item->runtime : null,
            'number_of_seasons' => $isMovie ? null : $item->number_of_seasons,
            'number_of_episodes' => $isMovie ? null : $item->number_of_episodes,
            'trailer' => [
                'key' => $item->trailer_key,
                'url' => $item->trailer_url,
            ],
            'genres' => $item->genres->map(fn ($genre) => [
                'id' => $genre->id,
                'name' => $genre->name,
                'slug' => $genre->slug,
            ])->values(),
            'keywords' => $item->keywords->map(fn ($keyword) => [
                'id' => $keyword->id,
                'name' => $keyword->name,
            ])->values(),
            'cast' => $item->cast->map(fn ($actor) => [
                'id' => $actor->id,
                'name' => $actor->name,
                'slug' => $actor->slug,
                'profile' => $actor->profile_path,
                'character' => $actor->pivot->character,
                'order' => $actor->pivot->order,
            ])->values(),
            'seasons' => $isMovie ? [] : $item->seasons->map(fn ($season) => [
                'id' => $season->id,
                'season_number' => $season->season_number,
                'episodes_count' => $season->episodes_count,
            ])->values(),
            'updated_at' => $item->updated_at,
        ]);
    }

    /**
     * Lightweight catalog feed used only by the web application's sitemap.
     * It intentionally excludes playback URLs, downloads and user data.
     */
    public function index(Request $request, string $type): JsonResponse
    {
        $model = match ($type) {
            'movies' => Movie::class,
            'series' => Serie::class,
            default => abort(404),
        };

        $perPage = max(1, min((int) $request->input('per_page', 500), 1000));

        $items = $model::query()
            ->whereNull('content_category_id')
            ->select(['id', 'slug', 'poster_path', 'updated_at'])
            ->orderBy('id')
            ->paginate($perPage);

        return response()->json($items);
    }

    public function sections(): JsonResponse
    {
        $sections = HomeSection::query()
            ->with('category:id,slug')
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->select(['id', 'content_category_id', 'slug', 'updated_at'])
            ->orderBy('id')
            ->get()
            ->map(fn (HomeSection $section) => [
                'slug' => $section->slug,
                'category_slug' => $section->category?->slug,
                'updated_at' => $section->updated_at,
            ]);

        return response()->json(['data' => $sections]);
    }
}

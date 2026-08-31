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
            ->with('genres:id,name,slug')
            ->where(is_numeric($idOrSlug) ? 'id' : 'slug', $idOrSlug)
            ->firstOrFail();

        return response()->json([
            'id' => $item->id,
            'slug' => $item->slug,
            'title' => $isMovie ? $item->title : $item->name,
            'overview' => $item->overview,
            'poster' => $item->poster_path,
            'backdrop' => $item->backdrop_path,
            'year' => $isMovie ? $item->release_year : $item->first_air_year,
            'rating' => $item->rating,
            'genres' => $item->genres->map(fn ($genre) => [
                'name' => $genre->name,
                'slug' => $genre->slug,
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
            ->select(['id', 'slug', 'updated_at'])
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

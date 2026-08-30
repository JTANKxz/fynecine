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

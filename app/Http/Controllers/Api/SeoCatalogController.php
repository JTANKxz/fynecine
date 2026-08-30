<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Serie;
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
}

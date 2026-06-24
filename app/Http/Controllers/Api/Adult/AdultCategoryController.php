<?php

namespace App\Http\Controllers\Api\Adult;

use App\Http\Controllers\Controller;
use App\Models\AdultCategory;
use App\Models\AdultGallery;
use App\Models\AdultMedia;
use Illuminate\Http\Request;

class AdultCategoryController extends Controller
{
    public function show($slug)
    {
        $category = AdultCategory::where('slug', $slug)->firstOrFail();

        // Get videos related to this category (either directly or via gallery)
        $videos = AdultMedia::where('is_active', true)
            ->where('type', 'video')
            ->where(function($q) use ($category) {
                $q->where('adult_category_id', $category->id)
                  ->orWhereHas('gallery', function($g) use ($category) {
                      $g->where('adult_category_id', $category->id);
                  });
            })
            ->orderByDesc('id')
            ->get();

        // Get photos related to this category (either directly or via gallery)
        $photos = AdultMedia::where('is_active', true)
            ->where('type', 'photo')
            ->where(function($q) use ($category) {
                $q->where('adult_category_id', $category->id)
                  ->orWhereHas('gallery', function($g) use ($category) {
                      $g->where('adult_category_id', $category->id);
                  });
            })
            ->orderByDesc('id')
            ->get();

        // Get galleries related to this category
        $galleries = AdultGallery::where('is_active', true)
            ->where('adult_category_id', $category->id)
            ->withCount('media')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'category' => $category,
            'videos' => $videos,
            'photos' => $photos,
            'galleries' => $galleries,
        ]);
    }
}

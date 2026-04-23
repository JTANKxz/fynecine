<?php

namespace App\Http\Controllers\Api\Adult;

use App\Http\Controllers\Controller;
use App\Models\AdultMedia;
use App\Models\AdultGallery;
use App\Models\AdultModel;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'video'); // video or image
        $perPage = $request->get('per_page', 24);

        $media = AdultMedia::where('is_active', true)
            ->where('type', $type)
            ->whereNull('adult_gallery_id')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json($media);
    }

    public function show($id)
    {
        $media = AdultMedia::with(['gallery.model', 'gallery.category'])->findOrFail($id);
        
        // Get model and category from gallery if available
        $modelId = $media->adult_model_id ?: ($media->gallery ? $media->gallery->adult_model_id : null);
        $categoryId = $media->adult_category_id ?: ($media->gallery ? $media->gallery->adult_category_id : null);
        
        $relatedQuery = AdultMedia::where('id', '!=', $media->id)
            ->where('is_active', true)
            ->where('type', 'video');

        if ($categoryId) {
            $relatedQuery->where(function($q) use ($categoryId) {
                $q->where('adult_category_id', $categoryId)
                  ->orWhereHas('gallery', function($g) use ($categoryId) {
                      $g->where('adult_category_id', $categoryId);
                  });
            });
        }

        $relatedMedia = $relatedQuery->orderByDesc('id')
            ->limit(20)
            ->get();

        return response()->json([
            'media' => $media,
            'model' => $media->gallery ? $media->gallery->model : null,
            'category' => $media->gallery ? $media->gallery->category : null,
            'related_media' => $relatedMedia
        ]);
    }
}

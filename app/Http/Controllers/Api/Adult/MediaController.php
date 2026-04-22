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
        $modelId = $media->gallery ? $media->gallery->adult_model_id : null;
        $categoryId = $media->gallery ? $media->gallery->adult_category_id : null;
        
        // Related media strategy: 
        // 1. Other media from the same model
        // 2. Media from the same category
        // 3. Recent media
        
        $relatedMedia = AdultMedia::where('id', '!=', $media->id)
            ->where('is_active', true)
            ->where('type', 'video')
            ->whereNull('adult_gallery_id')
            ->orderByDesc('id')
            ->limit(12)
            ->get();

        return response()->json([
            'media' => $media,
            'model' => $media->gallery ? $media->gallery->model : null,
            'related_media' => $relatedMedia
        ]);
    }
}

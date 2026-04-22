<?php

namespace App\Http\Controllers\Api\Adult;

use App\Http\Controllers\Controller;
use App\Models\AdultMedia;
use App\Models\AdultGallery;
use App\Models\AdultModel;
use Illuminate\Http\Request;

class MediaController extends Controller
{
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
            ->when($modelId, function($q) use ($modelId) {
                return $q->whereHas('gallery', function($sq) use ($modelId) {
                    $sq->where('adult_model_id', $modelId);
                });
            })
            ->orWhere(function($q) use ($media, $categoryId) {
                $q->where('id', '!=', $media->id)
                    ->where('is_active', true)
                    ->where('type', 'video')
                    ->when($categoryId, function($sq) use ($categoryId) {
                        return $sq->whereHas('gallery', function($ssq) use ($categoryId) {
                            $ssq->where('adult_category_id', $categoryId);
                        });
                    });
            })
            ->orderByRaw($modelId ? "FIELD(adult_gallery_id, (SELECT id FROM adult_galleries WHERE adult_model_id = $modelId)) DESC" : "id DESC")
            ->limit(12)
            ->get();

        return response()->json([
            'media' => $media,
            'model' => $media->gallery ? $media->gallery->model : null,
            'related_media' => $relatedMedia
        ]);
    }
}

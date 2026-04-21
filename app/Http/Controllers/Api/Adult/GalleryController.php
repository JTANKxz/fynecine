<?php

namespace App\Http\Controllers\Api\Adult;

use App\Http\Controllers\Controller;
use App\Models\AdultGallery;
use App\Models\AdultCategory;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = AdultGallery::where('is_active', true)->with(['model', 'category']);

        if ($request->has('category_id')) {
            $query->where('adult_category_id', $request->category_id);
        }

        if ($request->has('model_id')) {
            $query->where('adult_model_id', $request->model_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $galleries = $query->orderByDesc('created_at')->paginate(20);
        return response()->json($galleries);
    }

    public function show($idOrSlug)
    {
        $gallery = AdultGallery::where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->with(['model', 'category', 'media' => function($q) {
                $q->orderBy('order');
            }])
            ->firstOrFail();

        return response()->json($gallery);
    }

    public function categories()
    {
        $categories = AdultCategory::where('is_active', true)->orderBy('order')->get();
        return response()->json($categories);
    }
}

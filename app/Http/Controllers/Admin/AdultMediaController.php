<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdultMedia;
use App\Models\AdultGallery;
use App\Models\AdultModel;
use App\Models\AdultCategory;
use Illuminate\Http\Request;

class AdultMediaController extends Controller
{
    public function index()
    {
        // Standalone media are those without adult_gallery_id
        $media = AdultMedia::whereNull('adult_gallery_id')->orderBy('id', 'desc')->paginate(20);
        return view('admin.adult.media.index', compact('media'));
    }

    public function create()
    {
        $galleries = AdultGallery::orderBy('title')->get();
        $models = AdultModel::orderBy('name')->get();
        $categories = AdultCategory::orderBy('name')->get();
        return view('admin.adult.media.create', compact('galleries', 'models', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required',
            'type' => 'required|in:image,video',
            'adult_gallery_id' => 'nullable|exists:adult_galleries,id',
            'adult_model_id' => 'nullable|exists:adult_models,id',
            'adult_category_id' => 'nullable|exists:adult_categories,id',
        ]);

        AdultMedia::create([
            'adult_gallery_id' => $request->adult_gallery_id,
            'adult_model_id' => $request->adult_model_id,
            'adult_category_id' => $request->adult_category_id,
            'title' => $request->title,
            'url' => $request->url,
            'type' => $request->type,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        if ($request->adult_gallery_id) {
            return redirect()->route('admin.adult.galleries.edit', $request->adult_gallery_id)->with('success', 'Mídia adicionada à galeria.');
        }

        return redirect()->route('admin.adult.media.index')->with('success', 'Mídia avulsa criada com sucesso.');
    }

    public function edit(AdultMedia $media)
    {
        $galleries = AdultGallery::orderBy('title')->get();
        $models = AdultModel::orderBy('name')->get();
        $categories = AdultCategory::orderBy('name')->get();
        return view('admin.adult.media.edit', compact('media', 'galleries', 'models', 'categories'));
    }

    public function update(Request $request, AdultMedia $media)
    {
        $request->validate([
            'url' => 'required',
            'type' => 'required|in:image,video',
            'adult_gallery_id' => 'nullable|exists:adult_galleries,id',
            'adult_model_id' => 'nullable|exists:adult_models,id',
            'adult_category_id' => 'nullable|exists:adult_categories,id',
        ]);

        $media->update([
            'adult_gallery_id' => $request->adult_gallery_id,
            'adult_model_id' => $request->adult_model_id,
            'adult_category_id' => $request->adult_category_id,
            'title' => $request->title,
            'url' => $request->url,
            'type' => $request->type,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        if ($request->adult_gallery_id) {
            return redirect()->route('admin.adult.galleries.edit', $request->adult_gallery_id)->with('success', 'Mídia atualizada.');
        }

        return redirect()->route('admin.adult.media.index')->with('success', 'Mídia avulsa atualizada.');
    }

    public function destroy(AdultMedia $media)
    {
        $galleryId = $media->adult_gallery_id;
        $media->delete();

        if ($galleryId) {
            return redirect()->route('admin.adult.galleries.edit', $galleryId)->with('success', 'Mídia removida da galeria.');
        }

        return redirect()->route('admin.adult.media.index')->with('success', 'Mídia avulsa removida.');
    }
}

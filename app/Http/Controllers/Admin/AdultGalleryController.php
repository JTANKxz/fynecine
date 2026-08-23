<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdultGallery;
use App\Models\AdultModel;
use App\Models\AdultCategory;
use App\Models\AdultCollection;
use App\Models\AdultMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdultGalleryController extends Controller
{
    public function index()
    {
        $query = AdultGallery::with(['model', 'category'])->withCount('media');
        if (request('status') === 'active') {
            $query->where('is_active', true);
        } elseif (request('status') === 'inactive') {
            $query->where('is_active', false);
        }
        if (request('type') && in_array(request('type'), ['photo', 'video', 'both'], true)) {
            $query->where('type', request('type'));
        }
        if (request('q')) {
            $query->where('title', 'like', '%' . request('q') . '%');
        }

        $galleries = $query->orderBy('order')->orderByDesc('created_at')->get();
        return view('admin.adult.galleries.index', compact('galleries'));
    }

    public function bulkStatus(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:adult_galleries,id'],
            'status' => ['required', 'boolean'],
        ]);

        AdultGallery::whereIn('id', $data['ids'])->update(['is_active' => $data['status']]);

        return back()->with('success', 'Status das galerias atualizado.');
    }

    public function create()
    {
        $models = AdultModel::where('is_active', true)->get();
        $categories = AdultCategory::where('is_active', true)->get();
        $collections = AdultCollection::where('is_active', true)->get();
        return view('admin.adult.galleries.create', compact('models', 'categories', 'collections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:photo,video,both',
            'adult_model_id' => 'nullable|exists:adult_models,id',
            'adult_category_id' => 'nullable|exists:adult_categories,id',
            'adult_collection_id' => 'nullable|exists:adult_collections,id',
        ]);

        AdultGallery::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . uniqid(),
            'description' => $request->description,
            'adult_model_id' => $request->adult_model_id,
            'adult_category_id' => $request->adult_category_id,
            'adult_collection_id' => $request->adult_collection_id,
            'type' => $request->type,
            'cover_url' => $request->cover_url,
            'is_active' => $request->has('is_active'),
            'order' => $request->order ?? 0,
        ]);

        return redirect()->route('admin.adult.galleries.index')->with('success', 'Galeria criada com sucesso.');
    }

    public function edit(AdultGallery $gallery)
    {
        $models = AdultModel::where('is_active', true)->get();
        $categories = AdultCategory::where('is_active', true)->get();
        $collections = AdultCollection::where('is_active', true)->get();
        return view('admin.adult.galleries.edit', [
            'gallery' => $gallery,
            'models' => $models,
            'categories' => $categories,
            'collections' => $collections
        ]);
    }

    public function update(Request $request, AdultGallery $gallery)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:photo,video,both',
            'adult_model_id' => 'nullable|exists:adult_models,id',
            'adult_category_id' => 'nullable|exists:adult_categories,id',
            'adult_collection_id' => 'nullable|exists:adult_collections,id',
        ]);

        $gallery->update([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . substr($gallery->slug, -5), // Manter final se possível ou regenerar
            'description' => $request->description,
            'adult_model_id' => $request->adult_model_id,
            'adult_category_id' => $request->adult_category_id,
            'adult_collection_id' => $request->adult_collection_id,
            'type' => $request->type,
            'cover_url' => $request->cover_url,
            'is_active' => $request->has('is_active'),
            'order' => $request->order ?? 0,
        ]);

        return redirect()->route('admin.adult.galleries.index')->with('success', 'Galeria atualizada com sucesso.');
    }

    public function destroy(AdultGallery $gallery)
    {
        $gallery->delete();
        return redirect()->route('admin.adult.galleries.index')->with('success', 'Galeria removida com sucesso.');
    }

    // Gerenciamento de Mídia
    public function media(AdultGallery $gallery)
    {
        $media = $gallery->media;
        return view('admin.adult.galleries.media', compact('gallery', 'media'));
    }

    public function addMedia(Request $request, AdultGallery $gallery)
    {
        $request->validate([
            'url' => 'required|string',
            'type' => 'required|in:image,video',
        ]);

        $gallery->media()->create([
            'title' => $request->title,
            'url' => $request->url,
            'type' => $request->type,
            'order' => $request->order ?? 0
        ]);

        return back()->with('success', 'Mídia adicionada com sucesso.');
    }

    public function removeMedia(AdultMedia $media)
    {
        $media->delete();
        return back()->with('success', 'Mídia removida.');
    }
}

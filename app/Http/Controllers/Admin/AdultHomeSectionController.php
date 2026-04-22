<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdultHomeSection;
use App\Models\AdultHomeSectionItem;
use App\Models\AdultGallery;
use App\Models\AdultMedia;
use App\Models\AdultModel;
use App\Models\AdultCollection;
use Illuminate\Http\Request;

class AdultHomeSectionController extends Controller
{
    public function index()
    {
        $sections = AdultHomeSection::orderBy('order')->get();
        return view('admin.adult.home-sections.index', compact('sections'));
    }

    public function create()
    {
        return view('admin.adult.home-sections.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:trending,recent,models_carousel,video_grid,photo_grid,galleries_grid,collections,categories_grid,custom',
        ]);

        AdultHomeSection::create([
            'title' => $request->title,
            'type' => $request->type,
            'order' => $request->order ?? 0,
            'limit' => $request->limit ?? 15,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult.home-sections.index')->with('success', 'Seção criada com sucesso.');
    }

    public function edit(AdultHomeSection $home_section)
    {
        return view('admin.adult.home-sections.edit', ['section' => $home_section]);
    }

    public function update(Request $request, AdultHomeSection $home_section)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:trending,recent,models_carousel,video_grid,photo_grid,galleries_grid,collections,categories_grid,custom',
        ]);

        $home_section->update([
            'title' => $request->title,
            'type' => $request->type,
            'order' => $request->order ?? 0,
            'limit' => $request->limit ?? 15,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult.home-sections.index')->with('success', 'Seção atualizada com sucesso.');
    }

    public function destroy(AdultHomeSection $home_section)
    {
        $home_section->delete();
        return redirect()->route('admin.adult.home-sections.index')->with('success', 'Seção removida com sucesso.');
    }

    public function toggle(AdultHomeSection $home_section)
    {
        $home_section->is_active = !$home_section->is_active;
        $home_section->save();
        return response()->json(['status' => true, 'is_active' => $home_section->is_active]);
    }

    public function manageItems(AdultHomeSection $home_section)
    {
        $items = $home_section->manualItems;
        $galleries = AdultGallery::orderBy('title')->get();
        $media = AdultMedia::whereNull('adult_gallery_id')->orderBy('id', 'desc')->get();
        $models = AdultModel::orderBy('name')->get();
        $collections = AdultCollection::orderBy('title')->get();

        return view('admin.adult.home-sections.manage-items', [
            'section' => $home_section,
            'items' => $items,
            'galleries' => $galleries,
            'media' => $media,
            'models' => $models,
            'collections' => $collections
        ]);
    }

    public function addItem(Request $request, AdultHomeSection $home_section)
    {
        $request->validate([
            'item_type' => 'required|in:gallery,media,model,collection',
            'item_id' => 'required|integer'
        ]);

        $home_section->manualItems()->create([
            'item_type' => $request->item_type,
            'item_id' => $request->item_id,
            'order' => $request->order ?? 0
        ]);

        return back()->with('success', 'Item adicionado à seção.');
    }

    public function removeItem(AdultHomeSectionItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item removido da seção.');
    }
}

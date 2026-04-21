<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdultHomeSection;
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
            'type' => 'required|in:trending,recent,models_carousel,video_grid,photo_grid,collections,categories_grid,custom',
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

    public function edit(AdultHomeSection $section)
    {
        return view('admin.adult.home-sections.edit', ['section' => $section]);
    }

    public function update(Request $request, AdultHomeSection $section)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:trending,recent,models_carousel,video_grid,photo_grid,collections,categories_grid,custom',
        ]);

        $section->update([
            'title' => $request->title,
            'type' => $request->type,
            'order' => $request->order ?? 0,
            'limit' => $request->limit ?? 15,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult.home-sections.index')->with('success', 'Seção atualizada com sucesso.');
    }

    public function destroy(AdultHomeSection $section)
    {
        $section->delete();
        return redirect()->route('admin.adult.home-sections.index')->with('success', 'Seção removida com sucesso.');
    }

    public function toggle(AdultHomeSection $section)
    {
        $section->is_active = !$section->is_active;
        $section->save();
        return response()->json(['status' => true, 'is_active' => $section->is_active]);
    }
}

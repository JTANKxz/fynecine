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
            'type' => 'required|in:trending,recent,models,galleries,categories,custom',
        ]);

        AdultHomeSection::create([
            'title' => $request->title,
            'type' => $request->type,
            'order' => $request->order ?? 0,
            'limit' => $request->limit ?? 15,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult-home-sections.index')->with('success', 'Seção criada com sucesso.');
    }

    public function edit(AdultHomeSection $adult_home_section)
    {
        return view('admin.adult.home-sections.edit', ['section' => $adult_home_section]);
    }

    public function update(Request $request, AdultHomeSection $adult_home_section)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:trending,recent,models,galleries,categories,custom',
        ]);

        $adult_home_section->update([
            'title' => $request->title,
            'type' => $request->type,
            'order' => $request->order ?? 0,
            'limit' => $request->limit ?? 15,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult-home-sections.index')->with('success', 'Seção atualizada com sucesso.');
    }

    public function destroy(AdultHomeSection $adult_home_section)
    {
        $adult_home_section->delete();
        return redirect()->route('admin.adult-home-sections.index')->with('success', 'Seção removida com sucesso.');
    }

    public function toggle(AdultHomeSection $section)
    {
        $section->is_active = !$section->is_active;
        $section->save();
        return response()->json(['status' => true, 'is_active' => $section->is_active]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdultCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdultCollectionController extends Controller
{
    public function index()
    {
        $collections = AdultCollection::orderBy('order')->get();
        return view('admin.adult.collections.index', compact('collections'));
    }

    public function create()
    {
        return view('admin.adult.collections.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'cover_url' => 'nullable|url',
        ]);

        AdultCollection::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(5),
            'description' => $request->description,
            'cover_url' => $request->cover_url,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult.collections.index')->with('success', 'Coleção criada com sucesso.');
    }

    public function edit(AdultCollection $collection)
    {
        return view('admin.adult.collections.edit', compact('collection'));
    }

    public function update(Request $request, AdultCollection $collection)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'cover_url' => 'nullable|url',
        ]);

        $collection->update([
            'title' => $request->title,
            'description' => $request->description,
            'cover_url' => $request->cover_url,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult.collections.index')->with('success', 'Coleção atualizada com sucesso.');
    }

    public function destroy(AdultCollection $collection)
    {
        $collection->delete();
        return redirect()->route('admin.adult.collections.index')->with('success', 'Coleção removida com sucesso.');
    }

    public function toggle(AdultCollection $collection)
    {
        $collection->is_active = !$collection->is_active;
        $collection->save();
        return response()->json(['status' => true, 'is_active' => $collection->is_active]);
    }
}

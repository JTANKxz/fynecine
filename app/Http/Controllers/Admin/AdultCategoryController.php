<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdultCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdultCategoryController extends Controller
{
    public function index()
    {
        $categories = AdultCategory::orderBy('order')->get();
        return view('admin.adult.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.adult.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'order' => 'integer'
        ]);

        AdultCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult.categories.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(AdultCategory $adult_category)
    {
        return view('admin.adult.categories.edit', ['category' => $adult_category]);
    }

    public function update(Request $request, AdultCategory $adult_category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'order' => 'integer'
        ]);

        $adult_category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.adult.categories.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(AdultCategory $adult_category)
    {
        $adult_category->delete();
        return redirect()->route('admin.adult.categories.index')->with('success', 'Categoria removida com sucesso.');
    }
}

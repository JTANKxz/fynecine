<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentCategoryController extends Controller
{
    public function index()
    {
        $categories = ContentCategory::orderBy('order')->get();
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'icon' => 'nullable',
            'order' => 'integer',
        ]);

        ContentCategory::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active'),
            'is_nav_visible' => $request->has('is_nav_visible')
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Categoria criada!');
    }

    public function edit(ContentCategory $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, ContentCategory $category)
    {
        $request->validate([
            'name' => 'required',
            'icon' => 'nullable',
            'order' => 'integer',
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'icon' => $request->icon,
            'order' => $request->order ?? 0,
            'is_active' => $request->has('is_active'),
            'is_nav_visible' => $request->has('is_nav_visible')
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Categoria atualizada!');
    }

    public function destroy(ContentCategory $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Categoria excluída!');
    }
}

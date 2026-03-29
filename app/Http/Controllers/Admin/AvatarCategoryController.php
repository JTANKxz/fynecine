<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AvatarCategory;
use Illuminate\Http\Request;

class AvatarCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = AvatarCategory::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.avatar-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.avatar-categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        AvatarCategory::create($validated);

        return redirect()->route('admin.avatar-categories.index')
            ->with('success', 'Categoria de avatar criada com sucesso!');
    }

    public function edit(AvatarCategory $avatar_category)
    {
        return view('admin.avatar-categories.edit', ['category' => $avatar_category]);
    }

    public function update(Request $request, AvatarCategory $avatar_category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $avatar_category->update($validated);

        return redirect()->route('admin.avatar-categories.index')
            ->with('success', 'Categoria de avatar atualizada com sucesso!');
    }

    public function destroy(AvatarCategory $avatar_category)
    {
        $avatar_category->delete();
        return redirect()->route('admin.avatar-categories.index')
            ->with('success', 'Categoria de avatar removida com sucesso!');
    }
}

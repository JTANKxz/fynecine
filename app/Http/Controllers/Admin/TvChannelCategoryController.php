<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TvChannel;
use App\Models\TvChannelCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TvChannelCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = TvChannelCategory::withCount('channels');

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $categories = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.channel-categories.index', compact('categories'));
    }

    public function create()
    {
        $channels = TvChannel::orderBy('name')->get();
        return view('admin.channel-categories.create', compact('channels'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'channels'   => 'nullable|array',
            'channels.*' => 'exists:tv_channels,id',
        ]);

        $category = TvChannelCategory::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        if (!empty($validated['channels'])) {
            $category->channels()->sync($validated['channels']);
        }

        return redirect()->route('admin.channel-categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(TvChannelCategory $category)
    {
        $channels = TvChannel::orderBy('name')->get();
        $category->load('channels');
        return view('admin.channel-categories.edit', compact('category', 'channels'));
    }

    public function update(Request $request, TvChannelCategory $category)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'channels'   => 'nullable|array',
            'channels.*' => 'exists:tv_channels,id',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
        ]);

        $category->channels()->sync($validated['channels'] ?? []);

        return redirect()->route('admin.channel-categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(TvChannelCategory $category)
    {
        $category->delete();
        return redirect()->route('admin.channel-categories.index')
            ->with('success', 'Categoria removida com sucesso!');
    }
}

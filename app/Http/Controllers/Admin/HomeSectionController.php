<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeSection;
use App\Models\HomeSectionItem;
use App\Models\Genre;
use App\Models\Network;
use App\Models\Movie;
use App\Models\Serie;
use Illuminate\Http\Request;

class HomeSectionController extends Controller
{
    public function index()
    {
        $sections = HomeSection::with(['genre', 'network'])
            ->orderBy('order')
            ->get();

        return view('admin.sections.index', compact('sections'));
    }

    public function create()
    {
        $genres = Genre::orderBy('name')->get();
        $networks = Network::orderBy('name')->get();
        return view('admin.sections.create', compact('genres', 'networks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'type'            => 'required|in:custom,genre,trending,network,recently_added',
            'content_type'    => 'required|in:movie,series,both',
            'genre_id'        => 'nullable|exists:genres,id',
            'network_id'      => 'nullable|exists:networks,id',
            'trending_period' => 'nullable|in:today,week,all_time',
            'limit'           => 'nullable|integer|min:1|max:50',
        ]);

        $validated['order'] = HomeSection::max('order') + 1;
        $validated['is_active'] = $request->boolean('is_active');

        HomeSection::create($validated);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Seção criada com sucesso!');
    }

    public function edit(HomeSection $section)
    {
        $genres = Genre::orderBy('name')->get();
        $networks = Network::orderBy('name')->get();
        return view('admin.sections.edit', compact('section', 'genres', 'networks'));
    }

    public function update(Request $request, HomeSection $section)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'type'            => 'required|in:custom,genre,trending,network,recently_added',
            'content_type'    => 'required|in:movie,series,both',
            'genre_id'        => 'nullable|exists:genres,id',
            'network_id'      => 'nullable|exists:networks,id',
            'trending_period' => 'nullable|in:today,week,all_time',
            'limit'           => 'nullable|integer|min:1|max:50',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $section->update($validated);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Seção atualizada!');
    }

    public function destroy(HomeSection $section)
    {
        $section->delete();
        return redirect()->route('admin.sections.index')
            ->with('success', 'Seção removida!');
    }

    /**
     * Toggle ativo/inativo via AJAX
     */
    public function toggle(HomeSection $section)
    {
        $section->update(['is_active' => !$section->is_active]);
        return back()->with('success', 'Status alterado!');
    }

    /**
     * Reordenar seções via AJAX (drag-and-drop)
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:home_sections,id',
        ]);

        foreach ($request->ids as $index => $id) {
            HomeSection::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    // ========== CUSTOM SECTION ITEMS ==========

    public function items(HomeSection $section)
    {
        $items = $section->items()->get();
        return view('admin.sections.items', compact('section', 'items'));
    }

    public function searchContent(Request $request)
    {
        $search = $request->input('q', '');
        $type = $request->input('type', 'movie');

        if ($type === 'movie') {
            $results = Movie::where('title', 'like', "%{$search}%")
                ->limit(10)->get(['id', 'title as name', 'poster_path']);
        } else {
            $results = Serie::where('name', 'like', "%{$search}%")
                ->limit(10)->get(['id', 'name', 'poster_path']);
        }

        return response()->json($results);
    }

    public function addItem(Request $request, HomeSection $section)
    {
        $validated = $request->validate([
            'content_id'   => 'required|integer',
            'content_type' => 'required|in:movie,series',
        ]);

        // Evitar duplicados
        $exists = $section->items()
            ->where('content_id', $validated['content_id'])
            ->where('content_type', $validated['content_type'])
            ->exists();

        if (!$exists) {
            $section->items()->create([
                'content_id'   => $validated['content_id'],
                'content_type' => $validated['content_type'],
                'order'        => $section->items()->max('order') + 1,
            ]);
        }

        return back()->with('success', 'Item adicionado!');
    }

    public function removeItem(HomeSectionItem $item)
    {
        $item->delete();
        return back()->with('success', 'Item removido!');
    }
}

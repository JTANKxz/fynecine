<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Short;
use App\Models\Movie;
use App\Models\Season;
use App\Models\Serie;
use App\Services\ShortSourceInspector;
use Illuminate\Http\Request;

class ShortController extends Controller
{
    public function index(Request $request)
    {
        $shorts = Short::query()->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%' . $request->search . '%'))
            ->latest()->paginate(20);
        return view('admin.shorts.index', compact('shorts'));
    }
    public function create() { return view('admin.shorts.form', ['short' => new Short]); }
    public function store(Request $request, ShortSourceInspector $inspector)
    {
        $short = Short::create($this->data($request, $inspector));
        return redirect()->route('admin.shorts.edit', $short)->with('success', 'Short criado e fonte identificada.');
    }
    public function edit(Short $short) { return view('admin.shorts.form', compact('short')); }
    public function update(Request $request, Short $short, ShortSourceInspector $inspector)
    {
        $short->update($this->data($request, $inspector));
        return back()->with('success', 'Short atualizado e fonte verificada.');
    }
    public function destroy(Short $short) { $short->delete(); return back()->with('success', 'Short removido.'); }
    public function inspect(Request $request, ShortSourceInspector $inspector)
    {
        $data = $request->validate(['url' => 'required|url|max:2048']);
        return response()->json($inspector->inspect($data['url']));
    }
    public function searchRelated(Request $request)
    {
        $term = trim((string) $request->query('q'));
        if (mb_strlen($term) < 2) return response()->json([]);
        $movies = Movie::where('title', 'like', "%{$term}%")->limit(8)->get()->map(fn ($item) => [
            'type' => 'movie', 'id' => $item->id, 'title' => $item->title, 'subtitle' => 'Filme' . ($item->release_year ? " · {$item->release_year}" : ''), 'poster' => $item->poster_path,
        ]);
        $series = Serie::where('name', 'like', "%{$term}%")->limit(8)->get()->map(fn ($item) => [
            'type' => 'series', 'id' => $item->id, 'title' => $item->name, 'subtitle' => 'Série' . ($item->first_air_year ? " · {$item->first_air_year}" : ''), 'poster' => $item->poster_path,
        ]);
        $seasons = Season::with('series')->whereHas('series', fn ($q) => $q->where('name', 'like', "%{$term}%"))->limit(12)->get()->map(fn ($item) => [
            'type' => 'season', 'id' => $item->id, 'title' => "{$item->series->name} · Temporada {$item->season_number}", 'subtitle' => 'Temporada', 'poster' => $item->series->poster_path,
        ]);
        return response()->json($movies->concat($series)->concat($seasons)->values());
    }
    private function data(Request $request, ShortSourceInspector $inspector): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:180', 'description' => 'nullable|string', 'source_url' => 'required|url|max:2048',
            'thumbnail_url' => 'nullable|url|max:2048', 'duration_seconds' => 'nullable|integer|min:0',
            'hashtags' => 'nullable|string|max:500', 'category' => 'nullable|string|max:80', 'language' => 'nullable|string|max:12', 'published_at' => 'nullable|date',
            'related_type' => 'nullable|in:movie,series,season', 'related_id' => 'nullable|integer',
        ]);
        $source = $inspector->inspect($data['source_url']);
        $related = $this->resolveRelated($data['related_type'] ?? null, $data['related_id'] ?? null);
        return array_merge($data, $source, $related, ['is_active' => $request->boolean('is_active')]);
    }
    private function resolveRelated(?string $type, ?int $id): array
    {
        if (!$type || !$id) return ['related_type' => null, 'related_id' => null, 'related_title' => null];
        $content = match ($type) {
            'movie' => Movie::findOrFail($id), 'series' => Serie::findOrFail($id), 'season' => Season::with('series')->findOrFail($id),
        };
        $title = match ($type) {
            'movie' => $content->title, 'series' => $content->name, 'season' => "{$content->series->name} · Temporada {$content->season_number}",
        };
        return ['related_type' => $type, 'related_id' => $id, 'related_title' => $title];
    }
}

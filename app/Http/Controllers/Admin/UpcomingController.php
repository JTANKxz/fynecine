<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Serie;
use App\Models\Upcoming;
use Illuminate\Http\Request;

class UpcomingController extends Controller
{
    use \App\Traits\ImportableContent;

    public function index()
    {
        $upcomings = Upcoming::with('serie')->orderBy('release_date', 'asc')->paginate(20);
        return view('admin.upcomings.index', compact('upcomings'));
    }

    public function seasons(string $tmdbId)
    {
        $response = $this->fetchTMDB("tv/$tmdbId", ['language' => 'pt-BR', 'append_to_response' => 'videos']);
        if (!$response->successful()) return response()->json(['error' => 'Serie nao encontrada no TMDB.'], 404);
        $data = $response->json();
        $seasons = collect($data['seasons'] ?? [])->filter(fn ($season) => ($season['season_number'] ?? 0) > 0)->map(fn ($season) => [
            'season_number' => $season['season_number'], 'name' => $season['name'] ?? 'Temporada ' . $season['season_number'],
            'air_date' => $season['air_date'] ?? null, 'poster_path' => $season['poster_path'] ?? null,
        ])->values();
        return response()->json(['title' => $data['name'] ?? 'Serie', 'seasons' => $seasons]);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'tmdb_id' => ['required'], 'type' => ['required', 'in:movie,tv'],
            'season_number' => ['nullable', 'integer', 'min:1'],
        ]);
        $tmdbId = $validated['tmdb_id']; $type = $validated['type']; $seasonNumber = $validated['season_number'] ?? null;
        $externalKey = $type === 'tv' && $seasonNumber ? "tv:$tmdbId:season:$seasonNumber" : ($type === 'tv' ? "tv:$tmdbId" : "movie:$tmdbId");
        if (Upcoming::where('external_key', $externalKey)->exists()) return response()->json(['success' => false, 'error' => 'Este lancamento ja esta em Em Breve.']);

        $endpoint = $type === 'tv' ? "tv/$tmdbId" : "movie/$tmdbId";
        $response = $this->fetchTMDB($endpoint, ['language' => 'pt-BR', 'append_to_response' => 'videos']);
        if (!$response->successful()) return response()->json(['success' => false, 'error' => 'Erro ao buscar no TMDB.']);
        $data = $response->json();
        $trailerKey = collect($data['videos']['results'] ?? [])->first(fn ($video) => ($video['site'] ?? '') === 'YouTube' && in_array($video['type'] ?? '', ['Trailer', 'Teaser']))['key'] ?? null;
        $season = $seasonNumber ? collect($data['seasons'] ?? [])->firstWhere('season_number', (int) $seasonNumber) : null;
        if ($seasonNumber && !$season) return response()->json(['success' => false, 'error' => 'Temporada nao encontrada no TMDB.'], 422);
        $series = $type === 'tv' ? Serie::where('tmdb_id', $tmdbId)->first() : null;
        $isSeason = $season !== null;
        $title = $isSeason ? (($data['name'] ?? 'Serie') . ' - ' . ($season['name'] ?? 'Temporada ' . $seasonNumber)) : ($data['title'] ?? $data['name']);
        $date = $isSeason ? ($season['air_date'] ?? null) : ($data['release_date'] ?? $data['first_air_date'] ?? null);
        $poster = $isSeason ? ($season['poster_path'] ?? null) : ($data['poster_path'] ?? null);
        $upcoming = Upcoming::create([
            'tmdb_id' => $tmdbId, 'external_key' => $externalKey, 'linked_serie_id' => $series?->id,
            'season_number' => $isSeason ? $seasonNumber : null, 'season_name' => $isSeason ? ($season['name'] ?? null) : null,
            'title' => $title, 'type' => $type === 'tv' ? 'series' : 'movie',
            'poster_path' => $poster ? "https://image.tmdb.org/t/p/w500$poster" : null,
            'backdrop_path' => !empty($data['backdrop_path']) ? 'https://image.tmdb.org/t/p/original' . $data['backdrop_path'] : null,
            'release_date' => $date, 'trailer_key' => $trailerKey,
        ]);
        return response()->json(['success' => true, 'upcoming' => $upcoming]);
    }

    public function destroy(Upcoming $upcoming)
    {
        $upcoming->delete();
        return back()->with('success', 'Removido dos lancamentos Em Breve.');
    }
}

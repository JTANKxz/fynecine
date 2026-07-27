<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Cast;

class TMDBController extends Controller
{
    use \App\Traits\ImportableContent;

    public function index()
    {
        return view('admin.tmdb.tmdb', ['castLimit' => \App\Models\AppConfig::getSettings()->tmdb_cast_limit ?? 10]);
    }

    public function search(Request $request)
    {
        try {
            $query = $request->query('query');
            $type = $request->query('type', 'movie');
            $page = $request->query('page', 1);
            $yearFrom = $request->query('yearFrom');
            $yearTo = $request->query('yearTo');

            if ($query) {
                $endpoint = "search/$type";
                $params = [
                    'query' => $query,
                    'language' => 'pt-BR',
                    'page' => $page,
                    'include_adult' => $request->query('adult', 'false') === 'true',
                ];

                if ($yearFrom) {
                    $yearKey = $type === 'movie' ? 'primary_release_year' : 'first_air_date_year';
                    $params[$yearKey] = $yearFrom;
                }

            } else {
                $endpoint = "discover/$type";
                $sort = $request->query('sortBy', 'popularity.desc');

                if ($type === 'tv') {
                    $sort = str_replace('release_date', 'first_air_date', $sort);
                }

                $params = [
                    'language' => 'pt-BR',
                    'page' => $page,
                    'sort_by' => $sort,
                    'with_genres' => $request->query('genre'),
                    'include_adult' => $request->query('adult', 'false') === 'true'
                ];

                if ($yearFrom) {
                    $yearKey = $type === 'movie' ? 'primary_release_date.gte' : 'first_air_date.gte';
                    $params[$yearKey] = $yearFrom . "-01-01";
                }

                if ($yearTo) {
                    $yearKey = $type === 'movie' ? 'primary_release_date.lte' : 'first_air_date.lte';
                    $params[$yearKey] = $yearTo . "-12-31";
                }
            }

            $response = $this->fetchTMDB($endpoint, $params);

            if (!$response->successful()) {
                $errorData = $response->json();
                $message = $errorData['status_message'] ?? 'Erro desconhecido no TMDB';
                return response()->json(['error' => $message], $response->status());
            }

            $data = $response->json();

            if (!isset($data['results'])) {
                return response()->json(['results' => [], 'page' => 1, 'total_pages' => 1]);
            }

            $tmdbIds = collect($data['results'])->pluck('id');

            if ($request->query('target') === 'upcoming') {
                $imported = \App\Models\Upcoming::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id');
            } else {
                if ($type === 'tv') {
                    $imported = Serie::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id');
                } else {
                    $imported = Movie::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id');
                }
            }

            foreach ($data['results'] as &$item) {
                $item['imported'] = $imported->contains($item['id']);
            }

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno ao buscar dados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function import(Request $request)
    {
        $tmdbId = $request->tmdb_id;
        $type = $request->type;
        $mode = $request->mode;
        $categoryId = $request->category_id;
        $importCast = $request->input('import_cast', true);
        $castLimit = $request->integer('cast_limit') ?: null;

        if ($type === 'tv') {
            $fullImport = ($mode === 'full');
            $result = $this->performSeriesImport($tmdbId, $fullImport, $categoryId, $importCast, $castLimit);
            
            if (!$result['success']) {
                return response()->json(['error' => $result['error']], 404);
            }
            return response()->json($result);
        }

        return $this->importMovie($tmdbId, $categoryId, $importCast, $castLimit);
    }
    
    public function importMovie($tmdbId, $categoryId = null, $importCast = true, ?int $castLimit = null)
    {
        $result = $this->performMovieImport($tmdbId, $categoryId, $importCast, $castLimit);
        
        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 404);
        }
        
        return response()->json($result);
    }

    public function fetchSeasonsForSync($tmdbId)
    {
        try {
            $response = $this->fetchTMDB("tv/$tmdbId", ['language' => 'pt-BR']);
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Série não encontrada no TMDB'], 404);
            }

            $data = $response->json();
            $seasons = collect($data['seasons'] ?? [])->filter(function($season) {
                return $season['season_number'] > 0;
            })->values();

            return response()->json(['seasons' => $seasons]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function fetchEpisodesForSync($tmdbId, $seasonNumber)
    {
        try {
            $response = $this->fetchTMDB("tv/$tmdbId/season/$seasonNumber", ['language' => 'pt-BR']);
            
            if (!$response->successful()) {
                return response()->json(['error' => 'Temporada não encontrada no TMDB'], 404);
            }

            $data = $response->json();
            return response()->json(['episodes' => $data['episodes'] ?? []]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncSeasons(Request $request)
    {
        $tmdbId = $request->tmdb_id;
        $seriesId = $request->series_id;
        $seasons = $request->seasons;

        if (!$seasons || !is_array($seasons)) {
            return response()->json(['error' => 'Nenhuma temporada selecionada'], 400);
        }

        try {
            $results = [];
            foreach ($seasons as $seasonNumber) {
                $results[] = $this->importSeason($tmdbId, $seasonNumber, $seriesId);
            }
            return response()->json(['success' => true, 'results' => $results]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncEpisodes(Request $request)
    {
        $tmdbId = $request->tmdb_id;
        $seriesId = $request->series_id;
        $seasonId = $request->season_id;
        $seasonNumber = $request->season_number;
        $episodes = $request->episodes;

        if (!$episodes || !is_array($episodes)) {
            return response()->json(['error' => 'Nenhum episódio selecionado'], 400);
        }

        try {
            $response = $this->fetchTMDB("tv/$tmdbId/season/$seasonNumber", ['language' => 'pt-BR']);
            $seasonData = $response->json();

            $imported = 0;
            foreach ($seasonData['episodes'] as $episodeData) {
                if (in_array($episodeData['episode_number'], $episodes)) {
                    $baseImage = "https://image.tmdb.org/t/p/original";
                    Episode::updateOrCreate(
                        ['season_id' => $seasonId, 'episode_number' => $episodeData['episode_number']],
                        [
                            'series_id' => $seriesId,
                            'tmdb_id' => $episodeData['id'],
                            'name' => $episodeData['name'],
                            'overview' => $episodeData['overview'] ?? '',
                            'duration' => $episodeData['runtime'] ?? null,
                            'still_path' => ($episodeData['still_path'] ?? null) ? $baseImage . $episodeData['still_path'] : null,
                            'status' => 'active'
                        ]
                    );
                    $imported++;
                }
            }

            return response()->json(['success' => true, 'imported' => $imported]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateCastLimit(Request $request)
    {
        $validated = $request->validate(['cast_limit' => ['required', 'integer', 'min:1', 'max:30']]);
        $config = \App\Models\AppConfig::getSettings();
        $config->update(['tmdb_cast_limit' => $validated['cast_limit']]);
        return response()->json(['success' => true, 'cast_limit' => $config->tmdb_cast_limit]);
    }

    public function batchItems(Request $request)
    {
        $type = $request->validate(['type' => ['required', 'in:movie,tv']])['type'];
        $items = $type === 'movie'
            ? Movie::orderBy('id')->get(['id', 'tmdb_id', 'title'])->map(fn ($item) => ['id' => $item->id, 'tmdb_id' => $item->tmdb_id, 'title' => $item->title])
            : Serie::orderBy('id')->get(['id', 'tmdb_id', 'name'])->map(fn ($item) => ['id' => $item->id, 'tmdb_id' => $item->tmdb_id, 'title' => $item->name]);
        return response()->json(['items' => $items]);
    }

    public function refreshImported(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:movie,tv'], 'id' => ['required', 'integer'],
            'action' => ['required', 'in:details,cast,keywords'], 'cast_limit' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);
        $model = $validated['type'] === 'movie' ? Movie::findOrFail($validated['id']) : Serie::findOrFail($validated['id']);
        $limit = $validated['cast_limit'] ?? null;
        if ($validated['action'] === 'cast') {
            $this->syncCast($model, $validated['type'] === 'movie' ? 'movie' : 'tv', $model->tmdb_id, $this->resolveCastLimit($limit));
            return response()->json(['success' => true, 'message' => 'Elenco atualizado.']);
        }
        if ($validated['action'] === 'keywords') {
            $this->syncKeywords($model, $validated['type'] === 'movie' ? 'movie' : 'tv', $model->tmdb_id);
            return response()->json(['success' => true, 'message' => 'Palavras-chave atualizadas.']);
        }
        $result = $model instanceof Movie
            ? $this->refreshMovieFromTmdb($model, $limit)
            : $this->refreshSeriesFromTmdb($model, $limit);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

}
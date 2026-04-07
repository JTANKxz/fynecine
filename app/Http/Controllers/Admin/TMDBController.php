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
        return view('admin.tmdb.tmdb');
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

        if ($type === 'tv') {
            $fullImport = ($mode === 'full');
            $result = $this->performSeriesImport($tmdbId, $fullImport, $categoryId, $importCast);
            
            if (!$result['success']) {
                return response()->json(['error' => $result['error']], 404);
            }
            return response()->json($result);
        }

        return $this->importMovie($tmdbId, $categoryId, $importCast);
    }
    
    public function importMovie($tmdbId, $categoryId = null, $importCast = true)
    {
        $result = $this->performMovieImport($tmdbId, $categoryId, $importCast);
        
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
}
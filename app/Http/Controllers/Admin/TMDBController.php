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

            if ($type === 'tv') {
                $imported = Serie::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id');
            } else {
                $imported = Movie::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id');
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

        if ($type === 'tv') {
            $fullImport = ($mode === 'full');
            $result = $this->performSeriesImport($tmdbId, $fullImport);
            
            if (!$result['success']) {
                return response()->json(['error' => $result['error']], 404);
            }
            return response()->json($result);
        }

        return $this->importMovie($tmdbId);
    }
    
    public function importMovie($tmdbId)
    {
        $result = $this->performMovieImport($tmdbId);
        
        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 404);
        }
        
        return response()->json($result);
    }
}
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
    use \App\Traits\ImportableMovie;



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

            if ($mode === 'details') {
                return $this->importSeries($tmdbId, false);
            }

            if ($mode === 'full') {
                return $this->importSeries($tmdbId, true);
            }

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

    public function importSeries($tmdbId, $fullImport = false)
    {
        try {
            $response = $this->fetchTMDB("tv/$tmdbId", [
                'language' => 'pt-BR'
            ]);

            if (!$response->successful()) {
                return response()->json(['error' => 'Série não encontrada ou erro na API'], 404);
            }

            $data = $response->json();

        $name = $data['name'];

        $year = substr($data['first_air_date'], 0, 4);

        $slug = Str::slug($name);

        if (Serie::where('slug', $slug)->exists()) {
            $slug .= '-' . $year;
        }

        /*
        ====================
        BUSCAR TRAILER
        ====================
        */

        $videos = $this->fetchTMDB("tv/$tmdbId/videos", [
            'language' => 'pt-BR'
        ])->json();

        $trailerKey = null;

        if (isset($videos['results'])) {

            foreach ($videos['results'] as $video) {

                if ($video['type'] === 'Trailer' && $video['site'] === 'YouTube') {
                    $trailerKey = $video['key'];
                    break;
                }

            }

        }

        /*
        ====================
        CRIAR SÉRIE
        ====================
        */

        $existing = Serie::where('tmdb_id', $tmdbId)->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'series' => $existing
            ]);
        }

        $baseImage = "https://image.tmdb.org/t/p/original";

        $series = Serie::create([
            'tmdb_id' => $data['id'],
            'name' => $name,
            'slug' => $slug,
            'first_air_year' => $year,
            'last_air_year' => $data['last_air_date'] ? substr($data['last_air_date'], 0, 4) : null,
            'number_of_seasons' => $data['number_of_seasons'],
            'number_of_episodes' => $data['number_of_episodes'],
            'rating' => $data['vote_average'],
            'overview' => $data['overview'],
            'poster_path' => $data['poster_path'] ? $baseImage . $data['poster_path'] : null,
            'backdrop_path' => $data['backdrop_path'] ? $baseImage . $data['backdrop_path'] : null,
            'trailer_key' => $trailerKey,
            'trailer_url' => $trailerKey ? "https://www.youtube.com/watch?v=" . $trailerKey : null,
            'content_type' => 'series',
            'age_rating' => $this->getAgeRating('tv', $tmdbId)
        ]);

        /*
        ====================
        SALVAR GENEROS
        ====================
        */

        $genreIds = [];

        foreach ($data['genres'] as $genre) {

            $genreModel = Genre::updateOrCreate(
                ['tmdb_id' => $genre['id']],
                [
                    'name' => $genre['name'],
                    'slug' => Str::slug($genre['name'])
                ]
            );

            $genreIds[] = $genreModel->id;
        }

        $series->genres()->sync($genreIds);
        if ($fullImport) {

            foreach ($data['seasons'] as $seasonData) {

                // ignorar especiais
                if ($seasonData['season_number'] == 0) {
                    continue;
                }

                $this->importSeason($tmdbId, $seasonData['season_number'], $series->id);
            }
        }

        /*
====================
IMPORTAR ELENCO
====================
*/

        $credits = $this->fetchTMDB("tv/$tmdbId/credits", [
            'language' => 'pt-BR'
        ])->json();

        if (isset($credits['cast'])) {

            foreach (array_slice($credits['cast'], 0, 5) as $actor) {

                $cast = \App\Models\Cast::updateOrCreate(
                    ['tmdb_id' => $actor['id']],
                    [
                        'name' => $actor['name'],
                        'slug' => \Str::slug($actor['name']),
                        'profile_path' => $actor['profile_path']
                            ? "https://image.tmdb.org/t/p/w500" . $actor['profile_path']
                            : null
                    ]
                );

                $series->cast()->syncWithoutDetaching([
                    $cast->id => [
                        'character' => $actor['character'] ?? null,
                        'order' => $actor['order'] ?? 0
                    ]
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'series' => $series
        ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao importar série: ' . $e->getMessage()], 500);
        }
    }

    public function importSeason($tmdbId, $seasonNumber, $seriesId = null)
    {
        if (!$seriesId) {

            $series = Serie::where('tmdb_id', $tmdbId)->first();

            if (!$series) {

                $seriesResponse = $this->fetchTMDB("tv/$tmdbId", [
                    'language' => 'pt-BR'
                ]);

                $seriesData = $seriesResponse->json();

                $series = Serie::create([
                    'tmdb_id' => $seriesData['id'],
                    'name' => $seriesData['name'],
                    'slug' => Str::slug($seriesData['name']),
                    'first_air_year' => substr($seriesData['first_air_date'], 0, 4),
                    'overview' => $seriesData['overview'],
                    'poster_path' => $seriesData['poster_path'],
                    'backdrop_path' => $seriesData['backdrop_path']
                ]);
            }

            $seriesId = $series->id;
        }

        $response = $this->fetchTMDB("tv/$tmdbId/season/$seasonNumber", [
            'language' => 'pt-BR'
        ]);

        $seasonData = $response->json();

        /*
        ====================
        CRIAR TEMPORADA
        ====================
        */

        $season = Season::updateOrCreate(
            [
                'series_id' => $seriesId,
                'season_number' => $seasonNumber
            ],
            [
                'tmdb_id' => $seasonData['id'],
                'status' => 'active'
            ]
        );

        /*
        ====================
        IMPORTAR EPISODIOS
        ====================
        */

        foreach ($seasonData['episodes'] as $episodeData) {

            $baseImage = "https://image.tmdb.org/t/p/original";

            Episode::updateOrCreate(
                [
                    'season_id' => $season->id,
                    'episode_number' => $episodeData['episode_number']
                ],
                [
                    'series_id' => $seriesId,
                    'tmdb_id' => $episodeData['id'],
                    'name' => $episodeData['name'],
                    'overview' => $episodeData['overview'],
                    'duration' => $episodeData['runtime'] ?? null,
                    'still_path' => $episodeData['still_path'] ? $baseImage . $episodeData['still_path'] : null, // URL completa
                    'status' => 'active'
                ]
            );
        }

        return response()->json([
            'success' => true,
            'season' => $season
        ]);
    }
}
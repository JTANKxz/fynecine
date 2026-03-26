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
    public function index()
    {
        return view('admin.tmdb.tmdb');
    }

    public function search(Request $request)
    {
        $query = $request->query('query');
        $type = $request->query('type', 'movie');
        $page = $request->query('page', 1);

        if ($query) {

            $endpoint = "https://api.themoviedb.org/3/search/$type";

            $params = [
                'api_key' => 'edcd52275afd8b8c152c82f1ce3933a2',
                'query' => $query,
                'language' => 'pt-BR',
                'page' => $page
            ];

        } else {

            $endpoint = "https://api.themoviedb.org/3/discover/$type";
            $sort = $request->query('sortBy', 'popularity.desc');

            if ($type === 'tv') {
                $sort = str_replace('release_date', 'first_air_date', $sort);
            }

            $params = [
                'api_key' => 'edcd52275afd8b8c152c82f1ce3933a2',
                'language' => 'pt-BR',
                'page' => $page,
                'sort_by' => $sort,
                'with_genres' => $request->query('genre'),
                'include_adult' => $request->query('adult', false)
            ];
        }

        $response = Http::get($endpoint, $params)->json();

        $tmdbIds = collect($response['results'])->pluck('id');

        if ($type === 'tv') {
            $imported = Serie::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id');
        } else {
            $imported = Movie::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id');
        }

        foreach ($response['results'] as &$item) {
            $item['imported'] = $imported->contains($item['id']);
        }

        return response()->json($response);
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

        $response = Http::get("https://api.themoviedb.org/3/movie/$tmdbId", [
            'api_key' => 'edcd52275afd8b8c152c82f1ce3933a2',
            'language' => 'pt-BR'
        ]);

        $data = $response->json();

        if (!$data) {
            return response()->json(['error' => 'Filme não encontrado'], 404);
        }

        $title = $data['title'];
        $year = substr($data['release_date'], 0, 4);

        $slug = Str::slug($title);

        if (Movie::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . $year;
        }

        /*
        ====================
        BUSCAR TRAILER
        ====================
        */

        $videos = Http::get("https://api.themoviedb.org/3/movie/$tmdbId/videos", [
            'api_key' => 'edcd52275afd8b8c152c82f1ce3933a2',
            'language' => 'pt-BR'
        ])->json();

        $trailerKey = null;

        if (isset($videos['results'])) {

            foreach ($videos['results'] as $video) {

                if (
                    $video['type'] === 'Trailer' &&
                    $video['site'] === 'YouTube'
                ) {
                    $trailerKey = $video['key'];
                    break;
                }

            }

        }

        /*
        ====================
        CRIAR FILME
        ====================
        */

        $baseImage = "https://image.tmdb.org/t/p/original";

        $movie = Movie::create([
            'tmdb_id' => $data['id'],
            'imdb_id' => $data['imdb_id'],
            'title' => $title,
            'slug' => $slug,
            'release_year' => $year,
            'runtime' => $data['runtime'],
            'rating' => $data['vote_average'],
            'overview' => $data['overview'],
            'poster_path' => $data['poster_path'] ? $baseImage . $data['poster_path'] : null,
            'backdrop_path' => $data['backdrop_path'] ? $baseImage . $data['backdrop_path'] : null,
            'trailer_key' => $trailerKey,
            'trailer_url' => $trailerKey ? "https://www.youtube.com/watch?v=" . $trailerKey : null,
            'content_type' => 'movie'
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

        $movie->genres()->sync($genreIds);

        /*
====================
IMPORTAR ELENCO
====================
*/

        $credits = Http::get("https://api.themoviedb.org/3/movie/$tmdbId/credits", [
            'api_key' => 'edcd52275afd8b8c152c82f1ce3933a2',
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

                $movie->cast()->syncWithoutDetaching([
                    $cast->id => [
                        'character' => $actor['character'] ?? null,
                        'order' => $actor['order'] ?? 0
                    ]
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'movie' => $movie
        ]);
    }

    public function importSeries($tmdbId, $fullImport = false)
    {
        $response = Http::get("https://api.themoviedb.org/3/tv/$tmdbId", [
            'api_key' => 'edcd52275afd8b8c152c82f1ce3933a2',
            'language' => 'pt-BR'
        ]);

        $data = $response->json();

        if (!$data) {
            return response()->json(['error' => 'Série não encontrada'], 404);
        }

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

        $videos = Http::get("https://api.themoviedb.org/3/tv/$tmdbId/videos", [
            'api_key' => 'edcd52275afd8b8c152c82f1ce3933a2',
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
            'content_type' => 'series'
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

        $credits = Http::get("https://api.themoviedb.org/3/tv/$tmdbId/credits", [
            'api_key' => 'edcd52275afd8b8c152c82f1ce3933a2',
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
    }

    public function importSeason($tmdbId, $seasonNumber, $seriesId = null)
    {
        if (!$seriesId) {

            $series = Serie::where('tmdb_id', $tmdbId)->first();

            if (!$series) {

                $seriesResponse = Http::get("https://api.themoviedb.org/3/tv/$tmdbId", [
                    'api_key' => 'edcd52275afd8b8c152c82f1ce3933a2',
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

        $response = Http::get("https://api.themoviedb.org/3/tv/$tmdbId/season/$seasonNumber", [
            'api_key' => 'edcd52275afd8b8c152c82f1ce3933a2',
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
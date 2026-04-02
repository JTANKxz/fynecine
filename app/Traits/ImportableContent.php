<?php

namespace App\Traits;

use App\Models\Movie;
use App\Models\Serie;
use App\Models\Season;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Cast;
use App\Models\AppConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait ImportableContent
{
    private function fetchTMDB(string $endpoint, array $params = [])
    {
        $config = AppConfig::getSettings();
        $apiKey = $config->tmdb_key ?: env('TMDB_API_KEY', 'edcd52275afd8b8c152c82f1ce3933a2');

        $params['api_key'] = $apiKey;
        $params['endpoint'] = $endpoint;

        return Http::get('https://joetank.online/tmdb.php', $params);
    }

    private function getAgeRating(string $type, $tmdbId)
    {
        $endpoint = $type === 'movie' ? "movie/$tmdbId/release_dates" : "tv/$tmdbId/content_ratings";
        $params = $type === 'movie' ? ['region' => 'BR'] : [];
        $response = $this->fetchTMDB($endpoint, $params);

        if ($response->successful()) {
            $data = $response->json();
            $results = $data['results'] ?? [];

            foreach ($results as $result) {
                if (($result['iso_3166_1'] ?? '') === 'BR') {
                    if ($type === 'movie' && isset($result['release_dates'])) {
                        foreach ($result['release_dates'] as $rd) {
                            if (!empty($rd['certification'])) {
                                \Log::info("TMDB Age Rating Found for $type $tmdbId: " . $rd['certification']);
                                return $rd['certification'];
                            }
                        }
                    } elseif ($type === 'tv') {
                        \Log::info("TMDB Age Rating Found for $type $tmdbId: " . ($result['rating'] ?? 'null'));
                        return $result['rating'] ?? null;
                    }
                }
            }
        } else {
            \Log::error("TMDB Age Rating Fetch Failed for $type $tmdbId: " . $response->status());
        }

        return null;
    }

    public function performMovieImport($tmdbId, $categoryId = null)
    {
        try {
            $response = $this->fetchTMDB("movie/$tmdbId", [
                'language' => 'pt-BR'
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'Filme não encontrado ou erro na API'];
            }

            $data = $response->json();
            $title = $data['title'];
            $year = substr($data['release_date'] ?? '0000', 0, 4);
            $slug = Str::slug($title);

            if (Movie::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . $year;
            }

            // BUSCAR TRAILER
            $videos = $this->fetchTMDB("movie/$tmdbId/videos", [
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

            // CRIAR FILME
            $baseImage = "https://image.tmdb.org/t/p/original";

            $movie = Movie::create([
                'tmdb_id' => $data['id'],
                'imdb_id' => $data['imdb_id'] ?? null,
                'title' => $title,
                'slug' => $slug,
                'release_year' => $year,
                'runtime' => $data['runtime'] ?? 0,
                'rating' => $data['vote_average'] ?? 0,
                'overview' => $data['overview'] ?? '',
                'poster_path' => ($data['poster_path'] ?? null) ? $baseImage . $data['poster_path'] : null,
                'backdrop_path' => ($data['backdrop_path'] ?? null) ? $baseImage . $data['backdrop_path'] : null,
                'trailer_key' => $trailerKey,
                'trailer_url' => $trailerKey ? "https://www.youtube.com/watch?v=" . $trailerKey : null,
                'content_type' => 'movie',
                'age_rating' => $this->getAgeRating('movie', $tmdbId),
                'content_category_id' => $categoryId
            ]);

            // SALVAR GENEROS
            $genreIds = [];
            foreach (($data['genres'] ?? []) as $genre) {
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

            // IMPORTAR ELENCO
            $credits = $this->fetchTMDB("movie/$tmdbId/credits", [
                'language' => 'pt-BR'
            ])->json();

            if (isset($credits['cast'])) {
                foreach (array_slice($credits['cast'], 0, 5) as $actor) {
                    $cast = Cast::updateOrCreate(
                        ['tmdb_id' => $actor['id']],
                        [
                            'name' => $actor['name'],
                            'slug' => Str::slug($actor['name']),
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

            return ['success' => true, 'movie' => $movie];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Erro ao importar filme: ' . $e->getMessage()];
        }
    }

    public function performSeriesImport($tmdbId, $fullImport = false, $categoryId = null)
    {
        try {
            $response = $this->fetchTMDB("tv/$tmdbId", [
                'language' => 'pt-BR'
            ]);

            if (!$response->successful()) {
                return ['success' => false, 'error' => 'Série não encontrada ou erro na API'];
            }

            $data = $response->json();
            $name = $data['name'];
            $year = substr($data['first_air_date'] ?? '0000', 0, 4);
            $slug = Str::slug($name);

            if (Serie::where('slug', $slug)->exists()) {
                $slug .= '-' . $year;
            }

            // BUSCAR TRAILER
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

            // CRIAR SÉRIE
            $baseImage = "https://image.tmdb.org/t/p/original";

            $series = Serie::create([
                'tmdb_id' => $data['id'],
                'name' => $name,
                'slug' => $slug,
                'first_air_year' => $year,
                'last_air_year' => ($data['last_air_date'] ?? null) ? substr($data['last_air_date'], 0, 4) : null,
                'number_of_seasons' => $data['number_of_seasons'] ?? 0,
                'number_of_episodes' => $data['number_of_episodes'] ?? 0,
                'rating' => $data['vote_average'] ?? 0,
                'overview' => $data['overview'] ?? '',
                'poster_path' => ($data['poster_path'] ?? null) ? $baseImage . $data['poster_path'] : null,
                'backdrop_path' => ($data['backdrop_path'] ?? null) ? $baseImage . $data['backdrop_path'] : null,
                'trailer_key' => $trailerKey,
                'trailer_url' => $trailerKey ? "https://www.youtube.com/watch?v=" . $trailerKey : null,
                'content_type' => 'series',
                'age_rating' => $this->getAgeRating('tv', $tmdbId),
                'content_category_id' => $categoryId
            ]);

            // SALVAR GENEROS
            $genreIds = [];
            foreach (($data['genres'] ?? []) as $genre) {
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

            // IMPORTAR ELENCO
            $credits = $this->fetchTMDB("tv/$tmdbId/credits", [
                'language' => 'pt-BR'
            ])->json();

            if (isset($credits['cast'])) {
                foreach (array_slice($credits['cast'], 0, 5) as $actor) {
                    $cast = Cast::updateOrCreate(
                        ['tmdb_id' => $actor['id']],
                        [
                            'name' => $actor['name'],
                            'slug' => Str::slug($actor['name']),
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

            // IMPORTAR TEMPORADAS (Full Import)
            if ($fullImport && isset($data['seasons'])) {
                foreach ($data['seasons'] as $seasonData) {
                    if ($seasonData['season_number'] == 0) continue; // Pular especiais
                    $this->importSeason($tmdbId, $seasonData['season_number'], $series->id);
                }
            }

            return ['success' => true, 'series' => $series];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Erro ao importar série: ' . $e->getMessage()];
        }
    }

    public function importSeason($tmdbId, $seasonNumber, $seriesId = null)
    {
        if (!$seriesId) {
            $series = Serie::where('tmdb_id', $tmdbId)->first();
            if (!$series) {
                $response = $this->fetchTMDB("tv/$tmdbId", ['language' => 'pt-BR']);
                $seriesData = $response->json();
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

        $response = $this->fetchTMDB("tv/$tmdbId/season/$seasonNumber", ['language' => 'pt-BR']);
        $seasonData = $response->json();

        $season = Season::updateOrCreate(
            ['series_id' => $seriesId, 'season_number' => $seasonNumber],
            ['tmdb_id' => $seasonData['id'], 'status' => 'active']
        );

        foreach (($seasonData['episodes'] ?? []) as $episodeData) {
            $baseImage = "https://image.tmdb.org/t/p/original";
            Episode::updateOrCreate(
                ['season_id' => $season->id, 'episode_number' => $episodeData['episode_number']],
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
        }

        return ['success' => true, 'season' => $season];
    }
}

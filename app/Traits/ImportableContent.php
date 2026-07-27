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
        $apiKey = $config->tmdb_key ?: env('TMDB_API_KEY');

        // The TMDB v3 key remains server-side. The browser never receives it.
        $params['api_key'] = $apiKey;

        return Http::acceptJson()
            ->timeout(20)
            ->retry(2, 250)
            ->get('https://api.themoviedb.org/3/' . ltrim($endpoint, '/'), $params);
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

    public function performMovieImport($tmdbId, $categoryId = null, $importCast = true, ?int $castLimit = null)
    {
        try {
            $castLimit = $this->resolveCastLimit($castLimit);
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
                'vote_count' => $data['vote_count'] ?? 0,
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

            $this->syncKeywords($movie, 'movie', $tmdbId);
            if ($importCast) {
                $this->syncCast($movie, 'movie', $tmdbId, $castLimit);
            }

            return ['success' => true, 'movie' => $movie];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Erro ao importar filme: ' . $e->getMessage()];
        }
    }

    public function performSeriesImport($tmdbId, $fullImport = false, $categoryId = null, $importCast = true, ?int $castLimit = null)
    {
        try {
            $castLimit = $this->resolveCastLimit($castLimit);
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
                'vote_count' => $data['vote_count'] ?? 0,
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

            $this->syncKeywords($series, 'tv', $tmdbId);
            if ($importCast) {
                $this->syncCast($series, 'tv', $tmdbId, $castLimit);
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

    private function resolveCastLimit(?int $castLimit): int
    {
        $configured = AppConfig::getSettings()->tmdb_cast_limit ?? 10;
        return max(1, min(30, $castLimit ?? $configured));
    }

    private function syncCast($content, string $type, int $tmdbId, int $castLimit): void
    {
        $credits = $this->fetchTMDB(($type === 'movie' ? "movie/$tmdbId" : "tv/$tmdbId") . '/credits', ['language' => 'pt-BR'])->json();
        $sync = [];
        foreach (array_slice($credits['cast'] ?? [], 0, $castLimit) as $actor) {
            $cast = Cast::updateOrCreate(['tmdb_id' => $actor['id']], [
                'name' => $actor['name'],
                'slug' => Str::slug($actor['name']),
                'profile_path' => !empty($actor['profile_path']) ? 'https://image.tmdb.org/t/p/w500' . $actor['profile_path'] : null,
            ]);
            $sync[$cast->id] = ['character' => $actor['character'] ?? null, 'order' => $actor['order'] ?? 0];
        }
        $content->cast()->sync($sync);
    }

    private function syncKeywords($content, string $type, int $tmdbId): void
    {
        $data = $this->fetchTMDB(($type === 'movie' ? "movie/$tmdbId" : "tv/$tmdbId") . '/keywords')->json();
        $items = $type === 'movie' ? ($data['keywords'] ?? []) : ($data['results'] ?? []);
        $ids = [];
        foreach ($items as $item) {
            $keyword = \App\Models\TmdbKeyword::updateOrCreate(['tmdb_id' => $item['id']], ['name' => $item['name']]);
            $ids[] = $keyword->id;
        }
        $content->keywords()->sync($ids);
    }

    public function refreshMovieFromTmdb(Movie $movie, ?int $castLimit = null, bool $syncCast = true, bool $syncKeywords = true): array
    {
        $data = $this->fetchTMDB("movie/{$movie->tmdb_id}", ['language' => 'pt-BR'])->json();
        if (empty($data['id'])) return ['success' => false, 'error' => 'Filme nao encontrado no TMDB.'];
        $base = 'https://image.tmdb.org/t/p/original';
        $movie->update([
            'title' => $data['title'] ?? $movie->title, 'release_year' => substr($data['release_date'] ?? '', 0, 4) ?: $movie->release_year,
            'runtime' => $data['runtime'] ?? $movie->runtime, 'rating' => $data['vote_average'] ?? $movie->rating,
            'vote_count' => $data['vote_count'] ?? $movie->vote_count, 'overview' => $data['overview'] ?? $movie->overview,
            'poster_path' => !empty($data['poster_path']) ? $base . $data['poster_path'] : $movie->poster_path,
            'backdrop_path' => !empty($data['backdrop_path']) ? $base . $data['backdrop_path'] : $movie->backdrop_path,
            'age_rating' => $this->getAgeRating('movie', $movie->tmdb_id),
        ]);
        $this->syncGenres($movie, $data['genres'] ?? []);
        if ($syncKeywords) $this->syncKeywords($movie, 'movie', $movie->tmdb_id);
        if ($syncCast) $this->syncCast($movie, 'movie', $movie->tmdb_id, $this->resolveCastLimit($castLimit));
        return ['success' => true, 'movie' => $movie->fresh()];
    }

    public function refreshSeriesFromTmdb(Serie $series, ?int $castLimit = null, bool $syncCast = true, bool $syncKeywords = true): array
    {
        $data = $this->fetchTMDB("tv/{$series->tmdb_id}", ['language' => 'pt-BR'])->json();
        if (empty($data['id'])) return ['success' => false, 'error' => 'Serie nao encontrada no TMDB.'];
        $base = 'https://image.tmdb.org/t/p/original';
        $series->update([
            'name' => $data['name'] ?? $series->name, 'first_air_year' => substr($data['first_air_date'] ?? '', 0, 4) ?: $series->first_air_year,
            'last_air_year' => !empty($data['last_air_date']) ? substr($data['last_air_date'], 0, 4) : $series->last_air_year,
            'number_of_seasons' => $data['number_of_seasons'] ?? $series->number_of_seasons, 'number_of_episodes' => $data['number_of_episodes'] ?? $series->number_of_episodes,
            'rating' => $data['vote_average'] ?? $series->rating, 'vote_count' => $data['vote_count'] ?? $series->vote_count,
            'overview' => $data['overview'] ?? $series->overview, 'poster_path' => !empty($data['poster_path']) ? $base . $data['poster_path'] : $series->poster_path,
            'backdrop_path' => !empty($data['backdrop_path']) ? $base . $data['backdrop_path'] : $series->backdrop_path,
            'age_rating' => $this->getAgeRating('tv', $series->tmdb_id),
        ]);
        $this->syncGenres($series, $data['genres'] ?? []);
        if ($syncKeywords) $this->syncKeywords($series, 'tv', $series->tmdb_id);
        if ($syncCast) $this->syncCast($series, 'tv', $series->tmdb_id, $this->resolveCastLimit($castLimit));
        return ['success' => true, 'series' => $series->fresh()];
    }

    private function syncGenres($content, array $genres): void
    {
        $ids = [];
        foreach ($genres as $genre) {
            $model = Genre::updateOrCreate(['tmdb_id' => $genre['id']], ['name' => $genre['name'], 'slug' => Str::slug($genre['name'])]);
            $ids[] = $model->id;
        }
        $content->genres()->sync($ids);
    }


}

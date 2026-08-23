<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Movie;
use App\Models\Genre;
use App\Models\Cast;

class TMDBController extends Controller
{
    use \App\Traits\ImportableContent;

    public function index()
    {
        return view('admin.tmdb.tmdb', [
            'castLimit' => \App\Models\AppConfig::getSettings()->tmdb_cast_limit ?? 10,
            'networks' => \App\Models\Network::orderBy('name')->get(['id', 'name']),
        ]);
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

    /**
     * Curadoria rápida do catálogo TMDb para o painel administrativo.
     *
     * Esta rota mantém a chave do TMDb no servidor e aplica a mesma indicação
     * de "já importado" usada pela busca manual.
     */
    public function radar(Request $request)
    {
        $collection = $request->validate([
            'collection' => ['required', 'in:trending_movies,trending_series,popular_movies,now_playing,popular_series,on_the_air,upcoming_series'],
            'page' => ['nullable', 'integer', 'min:1', 'max:500'],
            'provider' => ['nullable', 'integer', 'min:1'],
        ])['collection'];

        $page = $request->integer('page', 1);
        $providerId = $request->integer('provider') ?: null;
        $today = now()->toDateString();
        $withinNinetyDays = now()->addDays(90)->toDateString();

        $collections = [
            'trending_movies' => ['endpoint' => 'trending/movie/day', 'type' => 'movie', 'label' => 'Filmes em alta agora', 'params' => []],
            'trending_series' => ['endpoint' => 'trending/tv/day', 'type' => 'tv', 'label' => 'Séries em alta agora', 'params' => []],
            'popular_movies' => ['endpoint' => 'movie/popular', 'type' => 'movie', 'label' => 'Filmes populares', 'params' => ['region' => 'BR']],
            'now_playing' => ['endpoint' => 'movie/now_playing', 'type' => 'movie', 'label' => 'Em cartaz no cinema', 'params' => ['region' => 'BR']],
            'popular_series' => ['endpoint' => 'tv/popular', 'type' => 'tv', 'label' => 'Séries populares', 'params' => []],
            'on_the_air' => ['endpoint' => 'tv/on_the_air', 'type' => 'tv', 'label' => 'Séries em exibição', 'params' => []],
            'upcoming_series' => ['endpoint' => 'discover/tv', 'type' => 'tv', 'label' => 'Próximas estreias de séries', 'params' => [
                'sort_by' => 'popularity.desc',
                'first_air_date.gte' => $today,
                'first_air_date.lte' => $withinNinetyDays,
            ]],
        ];

        $definition = $collections[$collection];

        // Os endpoints de tendência e cartaz não aceitam filtros de provedor.
        // Quando o admin seleciona uma plataforma, trocamos para Discover para
        // retornar somente títulos incluídos no catálogo daquele streaming no BR.
        if ($providerId) {
            $definition['endpoint'] = 'discover/' . $definition['type'];
            $definition['label'] = 'Disponível no streaming selecionado';
            $definition['params'] = [
                'sort_by' => 'popularity.desc',
                'watch_region' => 'BR',
                'with_watch_providers' => $providerId,
                'with_watch_monetization_types' => 'flatrate',
            ];
        }
        $response = $this->fetchTMDB($definition['endpoint'], array_merge($definition['params'], [
            'language' => 'pt-BR',
            'page' => $page,
            'include_adult' => false,
        ]));

        if (!$response->successful()) {
            return response()->json([
                'error' => $response->json('status_message') ?? 'Não foi possível carregar o radar do TMDb.',
            ], $response->status());
        }

        $data = $response->json();
        $results = $this->filterRadarResults($data['results'] ?? []);
        $tmdbIds = collect($results)->pluck('id');
        $imported = $definition['type'] === 'movie'
            ? Movie::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id')
            : Serie::whereIn('tmdb_id', $tmdbIds)->pluck('tmdb_id');

        $results = collect($results)->map(function (array $item) use ($imported) {
            $item['imported'] = $imported->contains($item['id']);
            return $item;
        })->values();

        return response()->json([
            'collection' => $collection,
            'label' => $definition['label'],
            'type' => $definition['type'],
            'results' => $results,
            'page' => $data['page'] ?? $page,
            'total_pages' => $data['total_pages'] ?? 1,
            'provider_id' => $providerId,
        ]);
    }

    /**
     * Lista de provedores usada pelo filtro do Radar. O resultado é cacheado
     * para não adicionar uma chamada ao TMDb toda vez que o admin abre a tela.
     */
    public function radarProviders()
    {
        $providers = Cache::remember('tmdb.radar.providers.br', now()->addHours(12), function () {
            $responses = [
                $this->fetchTMDB('watch/providers/movie', ['language' => 'pt-BR', 'watch_region' => 'BR']),
                $this->fetchTMDB('watch/providers/tv', ['language' => 'pt-BR', 'watch_region' => 'BR']),
            ];

            return collect($responses)
                ->filter(fn ($response) => $response->successful())
                ->flatMap(fn ($response) => $response->json('results') ?? [])
                ->filter(fn (array $provider) => !empty($provider['provider_id']) && !empty($provider['provider_name']))
                ->unique('provider_id')
                ->sortBy(fn (array $provider) => mb_strtolower($provider['provider_name']))
                ->map(fn (array $provider) => [
                    'id' => $provider['provider_id'],
                    'name' => $provider['provider_name'],
                    'logo_path' => $provider['logo_path'] ?? null,
                ])
                ->values()
                ->all();
        });

        return response()->json(['providers' => $providers]);
    }

    /**
     * Remove produções indianas do radar editorial padrão solicitado pelo admin.
     * A busca manual continua sem este filtro para não esconder resultados buscados.
     */
    private function filterRadarResults(array $results): array
    {
        $indianLanguages = ['hi', 'ta', 'te', 'ml', 'kn', 'bn', 'mr', 'pa', 'gu', 'ur'];

        return collect($results)
            ->filter(function (array $item) use ($indianLanguages) {
                $countries = $item['origin_country'] ?? $item['production_countries'] ?? [];
                $countryCodes = collect($countries)->map(fn ($country) => is_array($country) ? ($country['iso_3166_1'] ?? null) : $country);

                return !in_array($item['original_language'] ?? null, $indianLanguages, true)
                    && !$countryCodes->contains('IN');
            })
            ->filter(fn (array $item) => !empty($item['poster_path']))
            ->values()
            ->all();
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'tmdb_id' => ['required', 'integer'],
            'type' => ['required', 'in:movie,tv'],
            'mode' => ['nullable', 'in:details,full'],
            'category_id' => ['nullable', 'exists:content_categories,id'],
            'network_id' => ['nullable', 'exists:networks,id'],
            'import_cast' => ['nullable', 'boolean'],
            'cast_limit' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $tmdbId = $validated['tmdb_id'];
        $type = $validated['type'];
        $mode = $validated['mode'] ?? 'full';
        $categoryId = $validated['category_id'] ?? null;
        $networkId = $validated['network_id'] ?? null;
        $importCast = $validated['import_cast'] ?? true;
        $castLimit = $validated['cast_limit'] ?? null;

        if ($type === 'tv') {
            $existingSeries = Serie::where('tmdb_id', $tmdbId)->first();
            if ($existingSeries) {
                $this->attachContentToNetwork($networkId, $existingSeries, 'series');
                return response()->json([
                    'success' => true,
                    'series' => $existingSeries,
                    'network_id' => $networkId,
                    'already_imported' => true,
                ]);
            }

            $fullImport = ($mode === 'full');
            $result = $this->performSeriesImport($tmdbId, $fullImport, $categoryId, $importCast, $castLimit);
            
            if (!$result['success']) {
                return response()->json(['error' => $result['error']], 404);
            }
            $this->attachContentToNetwork($networkId, $result['series'], 'series');
            return response()->json($result + ['network_id' => $networkId]);
        }

        $existingMovie = Movie::where('tmdb_id', $tmdbId)->first();
        if ($existingMovie) {
            $this->attachContentToNetwork($networkId, $existingMovie, 'movie');
            return response()->json([
                'success' => true,
                'movie' => $existingMovie,
                'network_id' => $networkId,
                'already_imported' => true,
            ]);
        }

        $result = $this->performMovieImport($tmdbId, $categoryId, $importCast, $castLimit);
        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 404);
        }

        $this->attachContentToNetwork($networkId, $result['movie'], 'movie');
        return response()->json($result + ['network_id' => $networkId]);
    }

    private function attachContentToNetwork(?int $networkId, $content, string $contentType): void
    {
        if (!$networkId || !$content) {
            return;
        }

        DB::table('network_content')->insertOrIgnore([
            'network_id' => $networkId,
            'content_id' => $content->id,
            'content_type' => $contentType,
        ]);
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
        $validated = $request->validate([
            'type' => ['required', 'in:movie,tv'],
            'missing_logo' => ['nullable', 'boolean'],
        ]);
        $type = $validated['type'];
        $model = $type === 'movie' ? Movie::class : Serie::class;
        $titleColumn = $type === 'movie' ? 'title' : 'name';
        $query = $model::query()->orderBy('id');

        if ($request->boolean('missing_logo')) {
            $query->where(function ($query) {
                $query->whereNull('logo_path')->orWhere('logo_path', '');
            });
        }

        $items = $query->get(['id', 'tmdb_id', $titleColumn])
            ->map(fn ($item) => ['id' => $item->id, 'tmdb_id' => $item->tmdb_id, 'title' => $item->{$titleColumn}]);
        return response()->json(['items' => $items]);
    }

    public function refreshImported(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:movie,tv'], 'id' => ['required', 'integer'],
            'action' => ['required', 'in:details,cast,keywords,logos'], 'cast_limit' => ['nullable', 'integer', 'min:1', 'max:30'],
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
        if ($validated['action'] === 'logos') {
            $logo = $this->fetchTmdbLogo($validated['type'] === 'movie' ? 'movie' : 'tv', $model->tmdb_id);
            if (!$logo) {
                return response()->json(['success' => true, 'message' => 'O TMDb não possui clear logo para este título.']);
            }
            $model->update(['logo_path' => $logo]);
            return response()->json(['success' => true, 'message' => 'Logo do título atualizado.']);
        }
        $result = $model instanceof Movie
            ? $this->refreshMovieFromTmdb($model, $limit)
            : $this->refreshSeriesFromTmdb($model, $limit);
        return response()->json($result, $result['success'] ? 200 : 422);
    }

}

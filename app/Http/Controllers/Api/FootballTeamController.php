<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Championship;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FootballTeamController extends Controller
{
    private const BASE_URL = 'https://webws.365scores.com/web/';

    /**
     * Perfil esportivo enxuto para o app: agenda, resultados recentes e a
     * posição do clube nas competições que já exibimos no Fynecine.
     */
    public function show(int $teamId): JsonResponse
    {
        try {
            $payload = Cache::remember("football:365scores:team:{$teamId}:v2", now()->addMinutes(10), function () use ($teamId) {
                $recent = $this->fetch('competitors/recentForm', [
                    'competitor' => $teamId,
                    'numOfGames' => 5,
                ]);
                $current = $this->fetch('games/current/', ['competitors' => $teamId]);
                $championships = Championship::query()->where('is_sports_enabled', true)
                    ->where('external_provider', '365scores')->whereNotNull('external_id')->get();
                $standings = $championships->map(function (Championship $championship) {
                    return ['championship' => $championship, 'source' => $this->fetch('standings/', [
                        'competitions' => $championship->external_id, 'live' => 'false', 'withSeasonsFilter' => 'true',
                    ])];
                });

                $team = $this->findTeam($teamId, $recent, $current, ...$standings->pluck('source')->all());
                if ($team === null) {
                    throw new \RuntimeException('Time não encontrado na fonte esportiva.');
                }

                $upcoming = collect(data_get($current, 'games', []))
                    ->filter(fn ($game) => is_array($game)
                        && (int) data_get($game, 'statusGroup') === 2
                        && $this->hasTeam($game, $teamId)
                        && filled(data_get($game, 'startTime'))
                        && Carbon::parse(data_get($game, 'startTime'))->isFuture())
                    ->map(fn (array $game) => $this->normalizeGame($game))
                    ->sortBy('starts_at')->take(8)->values()->all();

                $latest = collect(data_get($recent, 'games', []))
                    ->filter(fn ($game) => is_array($game) && (int) data_get($game, 'statusGroup') === 4 && $this->hasTeam($game, $teamId))
                    ->map(fn (array $game) => $this->normalizeGame($game))
                    ->sortByDesc('starts_at')->take(5)->values()->all();

                return [
                    'provider' => '365scores',
                    'updated_at' => now()->toIso8601String(),
                    'team' => $team,
                    'upcoming_games' => $upcoming,
                    'recent_games' => $latest,
                    'standings' => $standings->flatMap(fn (array $item) => $this->teamStandings($item['source'], $teamId, $item['championship']))->values(),
                ];
            });

            return response()->json($payload)->header('Cache-Control', 'public, max-age=300');
        } catch (\Throwable $exception) {
            Log::warning('Não foi possível carregar o perfil de time pelo 365Scores.', ['team_id' => $teamId, 'message' => $exception->getMessage()]);
            return response()->json(['message' => 'Não foi possível carregar este time agora.'], 503);
        }
    }

    /** @return array<string, mixed> */
    private function fetch(string $path, array $query = []): array
    {
        $response = Http::acceptJson()->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (compatible; FynecineSports/1.0)',
            'Accept-Language' => 'pt-BR,pt;q=0.9',
            'Referer' => 'https://www.365scores.com/',
        ])->connectTimeout(8)->timeout(15)->retry(2, 350, throw: false)->get(self::BASE_URL.$path, array_merge([
            'appTypeId' => 5, 'langId' => 31, 'timezoneName' => 'America/Sao_Paulo', 'userCountryId' => 21,
        ], $query));

        if (! $response->successful() || ! is_array($response->json())) {
            throw new \RuntimeException('365Scores respondeu HTTP '.$response->status());
        }

        return $response->json();
    }

    /** @return array<string, mixed>|null */
    private function findTeam(int $teamId, array ...$sources): ?array
    {
        foreach ($sources as $source) {
            foreach (data_get($source, 'competitors', []) as $team) {
                if (is_array($team) && (int) data_get($team, 'id') === $teamId) return $this->normalizeTeam($team);
            }
            foreach (data_get($source, 'games', []) as $game) {
                foreach ([data_get($game, 'homeCompetitor', []), data_get($game, 'awayCompetitor', [])] as $team) {
                    if (is_array($team) && (int) data_get($team, 'id') === $teamId) return $this->normalizeTeam($team);
                }
            }
        }
        return null;
    }

    /** @return array<int, array<string, mixed>> */
    private function teamStandings(array $source, int $teamId, Championship $fallback): array
    {
        $competition = data_get($source, 'competitions.0', []);
        $rows = data_get($source, 'standings.0.rows', []);
        return collect($rows)->filter(fn ($row) => is_array($row) && (int) data_get($row, 'competitor.id', data_get($row, 'team.id')) === $teamId)
            ->map(function (array $row) use ($competition) {
                return [
                    'competition' => ['id' => data_get($competition, 'id', $fallback->id), 'name' => data_get($competition, 'name', $fallback->name)],
                    'position' => $this->integer(data_get($row, 'position', data_get($row, 'rank'))),
                    'points' => $this->integer(data_get($row, 'points')),
                    'played' => $this->integer(data_get($row, 'gamePlayed', data_get($row, 'played'))),
                    'wins' => $this->integer(data_get($row, 'gamesWon', data_get($row, 'wins'))),
                    'draws' => $this->integer(data_get($row, 'gamesEven', data_get($row, 'draws'))),
                    'losses' => $this->integer(data_get($row, 'gamesLost', data_get($row, 'losses'))),
                ];
            })->values()->all();
    }

    /** @return array<string, mixed> */
    private function normalizeGame(array $game): array
    {
        $home = is_array(data_get($game, 'homeCompetitor')) ? data_get($game, 'homeCompetitor') : [];
        $away = is_array(data_get($game, 'awayCompetitor')) ? data_get($game, 'awayCompetitor') : [];
        return [
            'source_id' => $this->integer(data_get($game, 'id')),
            'competition' => data_get($game, 'competitionDisplayName', 'Campeonato'),
            'round' => $this->integer(data_get($game, 'roundNum')),
            'starts_at' => filled(data_get($game, 'startTime')) ? Carbon::parse(data_get($game, 'startTime'))->setTimezone('America/Sao_Paulo')->toIso8601String() : null,
            'status' => data_get($game, 'statusText'),
            'home_score' => $this->integer(data_get($home, 'score')),
            'away_score' => $this->integer(data_get($away, 'score')),
            'home_team' => $this->normalizeTeam($home),
            'away_team' => $this->normalizeTeam($away),
            'venue' => data_get($game, 'venue.name'),
        ];
    }

    /** @return array<string, mixed> */
    private function normalizeTeam(array $team): array
    {
        $id = $this->integer(data_get($team, 'id'));
        $version = $this->integer(data_get($team, 'imageVersion')) ?? 1;
        return ['source_id' => $id, 'name' => data_get($team, 'name', 'Time'), 'logo' => $id ? "https://imagecache.365scores.com/image/upload/f_png,w_82,h_82,c_limit,q_auto:eco,dpr_2,d_Competitors:default1.png/v{$version}/Competitors/{$id}" : null];
    }

    private function hasTeam(array $game, int $teamId): bool { return (int) data_get($game, 'homeCompetitor.id') === $teamId || (int) data_get($game, 'awayCompetitor.id') === $teamId; }
    private function integer(mixed $value): ?int { return is_numeric($value) ? (int) $value : null; }
}

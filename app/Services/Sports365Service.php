<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Event;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Sports365Service
{
    private const BASE_URL = 'https://webws.365scores.com/web/';
    private const PROVIDER = '365scores';

    /** @return array<string, mixed> */
    public function standings(Championship $championship): array
    {
        $competitionId = $this->competitionId($championship);
        $source = $this->fetch('standings/', [
            'competitions' => $competitionId,
            'live' => 'false',
            'withSeasonsFilter' => 'true',
        ], "sports:365:standings:{$competitionId}:v2", 20);

        $rows = collect($this->extractStandingRows($source))
            ->map(fn (array $row) => $this->normalizeStandingRow($row))
            ->filter(fn (array $row) => filled($row['team']['name']))
            ->sortBy('position')
            ->values();

        return [
            'updated_at' => now()->toIso8601String(),
            'name' => data_get($source, 'competitions.0.name', $championship->name),
            'rows' => $rows,
        ];
    }

    /** @return Collection<int, array<string, mixed>> */
    public function featuredCompetitions(?int $countryId = null): Collection
    {
        $source = $this->fetch('competitions/featured/', [
            'sports' => 1,
            'withSeasons' => 'true',
            'type' => 'stats',
        ], 'sports:365:featured-competitions:stats:v1', 360);
        $countries = collect(data_get($source, 'countries', []))->keyBy('id');

        return collect(data_get($source, 'competitions', []))
            ->filter(fn ($competition) => is_array($competition)
                && (int) ($competition['sportId'] ?? 0) === 1
                && (bool) ($competition['hasStandings'] ?? false)
                && (bool) ($competition['isActive'] ?? true))
            ->map(function (array $competition) use ($countries) {
                $country = $countries->get($competition['countryId'] ?? null, []);
                $season = collect($competition['seasons'] ?? [])
                    ->firstWhere('num', $competition['currentSeasonNum'] ?? null) ?? [];
                $stage = collect($season['stages'] ?? [])
                    ->firstWhere('num', $competition['currentStageNum'] ?? null) ?? [];

                return [
                    'source_id' => (int) $competition['id'],
                    'name' => $competition['name'],
                    'country_id' => isset($competition['countryId']) ? (int) $competition['countryId'] : null,
                    'country_name' => data_get($country, 'name'),
                    'sport_id' => 1,
                    'color' => $competition['color'] ?? null,
                    'has_live_standings' => (bool) ($competition['hasLiveStandings'] ?? false),
                    'has_stats' => (bool) ($competition['hasStats'] ?? false),
                    'has_brackets' => (bool) ($competition['hasBrackets'] ?? false),
                    'has_current_stage_standings' => (bool) ($competition['hasCurrentStageStandings'] ?? false),
                    'standings_name' => $competition['tableName'] ?? 'Classificação',
                    'brackets_name' => $competition['bracketsName'] ?? 'Mata-Mata',
                    'current_season_num' => isset($competition['currentSeasonNum']) ? (int) $competition['currentSeasonNum'] : null,
                    'current_season_name' => $season['name'] ?? null,
                    'current_stage_num' => isset($competition['currentStageNum']) ? (int) $competition['currentStageNum'] : null,
                    'current_stage_name' => $stage['name'] ?? null,
                    'stage_type' => isset($stage['stageType']) ? (int) $stage['stageType'] : null,
                    'image_version' => $competition['imageVersion'] ?? null,
                    'logo_url' => $this->competitionLogoUrl(
                        (int) $competition['id'],
                        isset($competition['countryId']) ? (int) $competition['countryId'] : null,
                        isset($competition['imageVersion']) ? (int) $competition['imageVersion'] : null,
                    ),
                ];
            })
            ->when($countryId, fn (Collection $items) => $items->where('country_id', $countryId))
            ->sortByDesc('has_live_standings')
            ->values();
    }

    /** @return Collection<int, array{id:int,name:string}> */
    public function featuredCountries(): Collection
    {
        $source = $this->fetch('competitions/featured/', [
            'sports' => 1,
            'withSeasons' => 'true',
            'type' => 'stats',
        ], 'sports:365:featured-competitions:stats:v1', 360);

        return collect(data_get($source, 'countries', []))
            ->filter(fn ($country) => isset($country['id'], $country['name']))
            ->map(fn (array $country) => ['id' => (int) $country['id'], 'name' => $country['name']])
            ->values();
    }

    /** @return Collection<int, array{id:int,name:string}> */
    public function countries(): Collection
    {
        $source = $this->fetch('countries/', [
            'sports' => 1,
        ], 'sports:365:countries:football:v1', 1440);

        return collect(data_get($source, 'countries', []))
            ->filter(fn ($country) => is_array($country) && isset($country['id'], $country['name']))
            ->map(fn (array $country) => ['id' => (int) $country['id'], 'name' => $country['name']])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function upcomingGames(Championship $championship, int $days = 30): Collection
    {
        $competitionId = $this->competitionId($championship);
        $source = $this->fetch('games/current/', [
            'competitions' => $competitionId,
        ], "sports:365:games:{$competitionId}", 30);
        $now = now('America/Sao_Paulo');

        return collect(data_get($source, 'games', []))
            ->filter(function ($game) use ($competitionId, $now, $days) {
                if (! is_array($game) || (int) ($game['competitionId'] ?? 0) !== $competitionId) {
                    return false;
                }

                if ((int) ($game['statusGroup'] ?? 0) !== 2 || empty($game['startTime'])) {
                    return false;
                }

                $startsAt = Carbon::parse($game['startTime'])->setTimezone('America/Sao_Paulo');
                return $startsAt->greaterThan($now) && $startsAt->lessThanOrEqualTo($now->copy()->addDays($days));
            })
            ->map(fn (array $game) => $this->normalizeGame($game))
            ->sortBy('starts_at')
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function recentGames(Championship $championship, int $limit = 4): Collection
    {
        $competitionId = $this->competitionId($championship);
        $source = $this->fetch('games/current/', ['competitions' => $competitionId], "sports:365:games:{$competitionId}", 30);

        return collect(data_get($source, 'games', []))
            ->filter(fn ($game) => is_array($game)
                && (int) ($game['competitionId'] ?? 0) === $competitionId
                && (int) ($game['statusGroup'] ?? 0) === 4)
            ->map(fn (array $game) => $this->normalizeGame($game) + [
                'status' => data_get($game, 'statusText'),
                'home_score' => $this->integer(data_get($game, 'homeCompetitor.score')),
                'away_score' => $this->integer(data_get($game, 'awayCompetitor.score')),
            ])
            ->sortByDesc('starts_at')->take(max(1, $limit))->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function teamRecentForm(Team $team, int $games = 5): Collection
    {
        if (! $team->external_id || $team->external_provider !== self::PROVIDER) {
            return collect();
        }

        $source = $this->fetch('competitors/recentForm', [
            'competitor' => $team->external_id,
            'numOfGames' => min(max($games, 1), 10),
        ], "sports:365:form:{$team->external_id}:{$games}", 180);

        return collect(data_get($source, 'games', []))
            ->filter(fn ($game) => is_array($game) && (int) ($game['statusGroup'] ?? 0) === 4)
            ->map(function (array $game) use ($team) {
                $home = data_get($game, 'homeCompetitor', []);
                $away = data_get($game, 'awayCompetitor', []);
                $isHome = (string) data_get($home, 'id') === (string) $team->external_id;
                $teamScore = $isHome ? data_get($home, 'score') : data_get($away, 'score');
                $opponentScore = $isHome ? data_get($away, 'score') : data_get($home, 'score');

                return [
                    'competition' => data_get($game, 'competitionDisplayName'),
                    'starts_at' => data_get($game, 'startTime'),
                    'opponent' => $isHome ? data_get($away, 'name') : data_get($home, 'name'),
                    'is_home' => $isHome,
                    'team_score' => is_numeric($teamScore) ? (int) $teamScore : null,
                    'opponent_score' => is_numeric($opponentScore) ? (int) $opponentScore : null,
                    'result' => data_get($isHome ? $home : $away, 'isWinner') ? 'W'
                        : (data_get($home, 'score') == data_get($away, 'score') ? 'D' : 'L'),
                ];
            })
            ->values();
    }

    /** @return Collection<int, array<string, mixed>> */
    public function teamUpcomingGames(Team $team): Collection
    {
        if (! $team->external_id || $team->external_provider !== self::PROVIDER) {
            return collect();
        }

        $source = $this->fetch('games/current/', [
            'competitors' => $team->external_id,
        ], "sports:365:team-games:{$team->external_id}", 30);
        $now = now('America/Sao_Paulo');

        return collect(data_get($source, 'games', []))
            ->filter(fn ($game) => is_array($game)
                && (int) ($game['statusGroup'] ?? 0) === 2
                && filled($game['startTime'] ?? null)
                && Carbon::parse($game['startTime'])->setTimezone('America/Sao_Paulo')->greaterThan($now))
            ->map(fn (array $game) => $this->normalizeGame($game))
            ->sortBy('starts_at')
            ->values();
    }

    /** Cria/atualiza os próximos jogos automáticos sem alterar eventos manuais. */
    public function syncUpcomingGames(Championship $championship): int
    {
        $games = $this->upcomingGames($championship);
        $count = 0;

        foreach ($games as $game) {
            $homeTeam = $this->upsertTeam($game['home_team']);
            $awayTeam = $this->upsertTeam($game['away_team']);
            $event = Event::firstOrNew([
                'external_provider' => self::PROVIDER,
                'external_id' => (string) $game['source_id'],
            ]);

            $event->fill([
                'title' => $championship->name,
                'description' => $game['round'] ? 'Rodada '.$game['round'] : null,
                'home_team' => $game['home_team']['name'],
                'away_team' => $game['away_team']['name'],
                'home_team_id' => $homeTeam?->id,
                'away_team_id' => $awayTeam?->id,
                'start_time' => Carbon::parse($game['starts_at'])->format('Y-m-d H:i:s'),
                'end_time' => Carbon::parse($game['starts_at'])->addHours(2)->format('Y-m-d H:i:s'),
                'championship_id' => $championship->id,
            ]);

            if (! $event->exists) {
                $event->is_active = true;
            }

            $event->save();
            $count++;
        }

        $championship->forceFill(['last_synced_at' => now()])->save();
        return $count;
    }

    /** @param array<string, mixed> $game @return array<string, mixed> */
    private function normalizeGame(array $game): array
    {
        $home = is_array($game['homeCompetitor'] ?? null) ? $game['homeCompetitor'] : [];
        $away = is_array($game['awayCompetitor'] ?? null) ? $game['awayCompetitor'] : [];

        return [
            'source_id' => (int) $game['id'],
            'competition' => $game['competitionDisplayName'] ?? 'Campeonato',
            'round' => isset($game['roundNum']) ? (int) $game['roundNum'] : null,
            'starts_at' => Carbon::parse($game['startTime'])->setTimezone('America/Sao_Paulo')->toIso8601String(),
            'venue' => data_get($game, 'venue.name'),
            'home_team' => $this->normalizeTeam($home),
            'away_team' => $this->normalizeTeam($away),
        ];
    }

    /** @param array<string, mixed> $team @return array<string, mixed> */
    private function normalizeTeam(array $team): array
    {
        $id = data_get($team, 'id');
        $version = data_get($team, 'imageVersion', 1);

        return [
            'source_id' => $id ? (int) $id : null,
            'name' => data_get($team, 'name', 'Time'),
            'logo_url' => $id
                ? "https://imagecache.365scores.com/image/upload/f_png,w_82,h_82,c_limit,q_auto:eco,dpr_2,d_Competitors:default1.png/v{$version}/Competitors/{$id}"
                : null,
        ];
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function normalizeStandingRow(array $row): array
    {
        $sourceTeam = is_array($row['competitor'] ?? null) ? $row['competitor'] : (is_array($row['team'] ?? null) ? $row['team'] : []);
        $team = $this->normalizeTeam($sourceTeam);
        $stats = is_array($row['stats'] ?? null) ? $row['stats'] : [];
        $for = $this->firstInteger([$row['for'] ?? null, $row['goalsFor'] ?? null, $stats['for'] ?? null, $stats['goalsFor'] ?? null]);
        $against = $this->firstInteger([$row['against'] ?? null, $row['goalsAgainst'] ?? null, $stats['against'] ?? null, $stats['goalsAgainst'] ?? null]);

        return [
            'position' => $this->firstInteger([$row['position'] ?? null, $row['rank'] ?? null, $row['number'] ?? null]),
            'team' => $team,
            'played' => $this->firstInteger([$row['gamePlayed'] ?? null, $row['played'] ?? null, $row['gamesPlayed'] ?? null, $stats['played'] ?? null]),
            'wins' => $this->firstInteger([$row['gamesWon'] ?? null, $row['wins'] ?? null, $stats['wins'] ?? null]),
            'draws' => $this->firstInteger([$row['gamesEven'] ?? null, $row['draws'] ?? null, $stats['draws'] ?? null]),
            'losses' => $this->firstInteger([$row['gamesLost'] ?? null, $row['losses'] ?? null, $stats['losses'] ?? null]),
            'goals_for' => $for, 'goals_against' => $against,
            'goal_difference' => $this->firstInteger([$row['ratio'] ?? null, $row['goalDifference'] ?? null, $stats['goalDifference'] ?? null]) ?? (($for !== null && $against !== null) ? $for - $against : null),
            'points' => $this->firstInteger([$row['points'] ?? null, $row['pts'] ?? null, $stats['points'] ?? null]),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function extractStandingRows(array $source): array
    {
        foreach (['standings.0.rows', 'standings.0.table.rows', 'standings.0.table', 'standings.rows', 'table.rows'] as $path) {
            $rows = data_get($source, $path);
            if (is_array($rows) && array_is_list($rows) && $rows !== []) return $rows;
        }
        return [];
    }

    private function firstInteger(array $values): ?int
    {
        foreach ($values as $value) if (is_numeric($value)) return (int) $value;
        return null;
    }

    /** @param array<string, mixed> $team */
    private function upsertTeam(array $team): ?Team
    {
        if (empty($team['name'])) {
            return null;
        }

        $sourceId = $team['source_id'] ?? null;
        $model = $sourceId
            ? Team::where('external_provider', self::PROVIDER)->where('external_id', (string) $sourceId)->first()
            : null;

        $model ??= Team::where('name', $team['name'])->first();
        $model ??= new Team();
        $model->fill([
            'name' => $team['name'],
            'external_provider' => self::PROVIDER,
            'external_id' => $sourceId ? (string) $sourceId : null,
        ]);

        if (blank($model->image_url) && filled($team['logo_url'] ?? null)) {
            $model->image_url = $team['logo_url'];
        }

        $model->save();
        return $model;
    }

    /** @return array<string, mixed> */
    private function fetch(string $path, array $query, string $cacheKey, int $minutes): array
    {
        return Cache::remember($cacheKey, now()->addMinutes($minutes), function () use ($path, $query) {
            $response = Http::acceptJson()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; FynecineSports/1.0)',
                    'Accept-Language' => 'pt-BR,pt;q=0.9',
                    'Referer' => 'https://www.365scores.com/',
                ])
                ->connectTimeout(8)
                ->timeout(15)
                ->retry(2, 350, throw: false)
                ->get(self::BASE_URL.$path, array_merge([
                    'appTypeId' => 5,
                    'langId' => 31,
                    'timezoneName' => 'America/Sao_Paulo',
                    'userCountryId' => 21,
                ], $query));

            if (! $response->successful() || ! is_array($response->json())) {
                throw new \RuntimeException('365Scores respondeu HTTP '.$response->status());
            }

            return $response->json();
        });
    }

    private function competitionId(Championship $championship): int
    {
        if ($championship->external_provider !== self::PROVIDER || ! $championship->external_id) {
            throw new \InvalidArgumentException('Configure o ID do 365Scores para este campeonato antes de sincronizar.');
        }

        return (int) $championship->external_id;
    }

    private function competitionLogoUrl(int $competitionId, ?int $countryId, ?int $imageVersion): ?string
    {
        if ($competitionId < 1 || ! $countryId || ! $imageVersion) {
            return null;
        }

        return "https://imagecache.365scores.com/image/upload/f_png,w_64,h_64,c_limit,q_auto:eco,dpr_2,d_Countries:Round:{$countryId}.png/v{$imageVersion}/Competitions/{$competitionId}";
    }

    private function integer(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}

<?php

namespace App\Services;

use App\Models\Championship;
use App\Models\Event;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrasileiraoScheduleService
{
    private const PROVIDER_URL = 'https://webws.365scores.com/web/games/current/';
    private const CACHE_KEY = 'football:365scores:brasileirao:upcoming-games:v2';
    private const PROVIDER = '365scores';
    private const COMPETITION_ID = 113;

    /**
     * Agenda futura do Brasileirão em um contrato estável para API e app.
     *
     * Não pedimos odds ao fornecedor, nem expomos links de casas de aposta.
     */
    public function upcomingGames(): Collection
    {
        try {
            return collect(Cache::remember(self::CACHE_KEY, now()->addMinutes(30), function () {
                $response = Http::acceptJson()
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (compatible; FynecineSports/1.0)',
                        'Accept-Language' => 'pt-BR,pt;q=0.9',
                        'Referer' => 'https://www.365scores.com/',
                    ])
                    ->connectTimeout(8)
                    ->timeout(15)
                    ->retry(2, 350, throw: false)
                    ->get(self::PROVIDER_URL, [
                        'appTypeId' => 5,
                        'langId' => 31,
                        'timezoneName' => 'America/Sao_Paulo',
                        'userCountryId' => 21,
                        'competitions' => self::COMPETITION_ID,
                    ]);

                if (! $response->successful() || ! is_array($response->json())) {
                    throw new \RuntimeException('365Scores respondeu HTTP '.$response->status());
                }

                $now = now('America/Sao_Paulo');

                return collect($response->json('games', []))
                    ->filter(function ($game) use ($now) {
                        if (! is_array($game) || (int) ($game['competitionId'] ?? 0) !== self::COMPETITION_ID) {
                            return false;
                        }

                        if ((int) ($game['statusGroup'] ?? 0) !== 2 || empty($game['startTime'])) {
                            return false;
                        }

                        $startsAt = Carbon::parse($game['startTime'])->setTimezone('America/Sao_Paulo');

                        return $startsAt->greaterThan($now) && $startsAt->lessThanOrEqualTo($now->copy()->addDays(30));
                    })
                    ->map(fn (array $game) => $this->normalizeGame($game))
                    ->sortBy('starts_at')
                    ->values()
                    ->all();
            }));
        } catch (\Throwable $exception) {
            Log::warning('Não foi possível atualizar a agenda do Brasileirão pelo 365Scores.', [
                'message' => $exception->getMessage(),
            ]);

            return collect(Cache::get(self::CACHE_KEY, []));
        }
    }

    /**
     * Cria ou atualiza apenas eventos gerados por esta fonte. Eventos manuais
     * nunca são removidos, sobrescritos ou duplicados por esta sincronização.
     */
    public function syncUpcomingGames(): int
    {
        $games = $this->upcomingGames();
        if ($games->isEmpty()) {
            return 0;
        }

        $championship = Championship::firstOrCreate(['name' => 'Brasileirão Série A']);
        $synced = 0;

        foreach ($games as $game) {
            $homeTeam = $this->findOrCreateTeam($game['home_team']['name']);
            $awayTeam = $this->findOrCreateTeam($game['away_team']['name']);
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
                'start_time' => Carbon::parse($game['starts_at'])->setTimezone('America/Sao_Paulo')->format('Y-m-d H:i:s'),
                'end_time' => Carbon::parse($game['starts_at'])->setTimezone('America/Sao_Paulo')->addHours(2)->format('Y-m-d H:i:s'),
                'championship_id' => $championship->id,
            ]);

            if (! $event->exists) {
                $event->is_active = true;
            }

            $event->save();
            $synced++;
        }

        return $synced;
    }

    /** @param array<string, mixed> $game @return array<string, mixed> */
    private function normalizeGame(array $game): array
    {
        $startsAt = Carbon::parse($game['startTime'])->setTimezone('America/Sao_Paulo');
        $home = is_array($game['homeCompetitor'] ?? null) ? $game['homeCompetitor'] : [];
        $away = is_array($game['awayCompetitor'] ?? null) ? $game['awayCompetitor'] : [];

        return [
            'source_id' => (int) $game['id'],
            'competition' => $game['competitionDisplayName'] ?? 'Brasileirão Série A',
            'round' => isset($game['roundNum']) ? (int) $game['roundNum'] : null,
            'starts_at' => $startsAt->toIso8601String(),
            'home_team' => [
                'source_id' => $home['id'] ?? null,
                'name' => $home['name'] ?? 'Mandante',
                'logo' => $this->teamLogoUrl($home['id'] ?? null, $home['imageVersion'] ?? null),
            ],
            'away_team' => [
                'source_id' => $away['id'] ?? null,
                'name' => $away['name'] ?? 'Visitante',
                'logo' => $this->teamLogoUrl($away['id'] ?? null, $away['imageVersion'] ?? null),
            ],
            'venue' => data_get($game, 'venue.name'),
        ];
    }

    private function findOrCreateTeam(?string $name): ?Team
    {
        if (blank($name)) {
            return null;
        }

        return Team::firstOrCreate(['name' => trim($name)]);
    }

    private function teamLogoUrl(mixed $teamId, mixed $imageVersion): ?string
    {
        if (! is_numeric($teamId)) {
            return null;
        }

        $version = is_numeric($imageVersion) ? (int) $imageVersion : 1;
        return "https://imagecache.365scores.com/image/upload/f_png,w_82,h_82,c_limit,q_auto:eco,dpr_2,d_Competitors:default1.png/v{$version}/Competitors/{$teamId}";
    }
}

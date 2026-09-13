<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FootballStandingsController extends Controller
{
    private const PROVIDER_URL = 'https://webws.365scores.com/web/standings/';
    private const CACHE_KEY = 'football:standings:365scores:brasileirao-serie-a:v1';

    /**
     * Tabela do Brasileirão Série A, normalizada para os clientes do Fynecine.
     *
     * O endpoint do 365Scores não é uma API pública versionada. Por isso o
     * cliente nunca recebe o formato original: esta camada mantém o contrato
     * do app estável mesmo quando o provedor acrescenta campos.
     */
    public function brasileirao(): JsonResponse
    {
        $cached = Cache::get(self::CACHE_KEY);

        try {
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
                    'competitions' => 113,
                    'live' => 'false',
                    'withSeasonsFilter' => 'true',
                ]);

            if (! $response->successful() || ! is_array($response->json())) {
                throw new \RuntimeException('365Scores respondeu HTTP '.$response->status());
            }

            $payload = $this->normalize($response->json());

            if (empty($payload['table'])) {
                throw new \RuntimeException('365Scores respondeu sem linhas de classificação.');
            }

            Cache::put(self::CACHE_KEY, $payload, now()->addMinutes(20));

            return response()->json(array_merge($payload, ['cached' => false]))
                ->header('Cache-Control', 'public, max-age=300');
        } catch (\Throwable $exception) {
            Log::warning('Não foi possível atualizar a classificação do Brasileirão pelo 365Scores.', [
                'message' => $exception->getMessage(),
            ]);

            if (is_array($cached)) {
                return response()->json(array_merge($cached, ['cached' => true]))
                    ->header('Cache-Control', 'public, max-age=300');
            }

            return response()->json([
                'message' => 'Não foi possível carregar a classificação no momento.',
                'provider' => '365scores',
            ], 503);
        }
    }

    /** @param array<string, mixed> $source */
    private function normalize(array $source): array
    {
        $competition = $this->firstArray($source, [
            'competition', 'competitions.0', 'data.competition', 'data.competitions.0',
        ]);

        $rows = $this->extractRows($source);

        return [
            'provider' => '365scores',
            'updated_at' => now()->toIso8601String(),
            'competition' => [
                'id' => data_get($competition, 'id', 113),
                'name' => data_get($competition, 'name', 'Brasileirão Série A'),
                'season' => data_get($competition, 'season.name')
                    ?? data_get($competition, 'currentSeason.name')
                    ?? data_get($source, 'season.name')
                    ?? data_get($source, 'currentSeason.name'),
                'logo' => data_get($competition, 'logo')
                    ?? data_get($competition, 'image')
                    ?? data_get($competition, 'imageUrl'),
            ],
            'table' => collect($rows)
                ->map(fn (array $row) => $this->normalizeRow($row))
                ->filter(fn (array $row) => filled($row['team']['name']))
                ->sortBy(fn (array $row) => $row['position'] ?? PHP_INT_MAX)
                ->values()
                ->all(),
        ];
    }

    /** @param array<string, mixed> $source @return array<int, array<string, mixed>> */
    private function extractRows(array $source): array
    {
        $paths = [
            'standings.0.table.rows', 'standings.0.table', 'standings.0.rows', 'standings.table.rows',
            'standings.rows', 'table.rows', 'data.standings.0.table.rows',
            'data.standings.0.table', 'data.standings.0.rows', 'data.standings.rows', 'data.table.rows',
            'tables.0.rows', 'data.tables.0.rows',
        ];

        foreach ($paths as $path) {
            $rows = data_get($source, $path);
            if (is_array($rows) && Arr::isList($rows) && $rows !== []) {
                return $rows;
            }
        }

        return $this->findRowsRecursively($source);
    }

    /** @param array<string, mixed> $value @return array<int, array<string, mixed>> */
    private function findRowsRecursively(array $value): array
    {
        foreach ($value as $key => $child) {
            if (! is_array($child)) {
                continue;
            }

            if (in_array((string) $key, ['rows', 'tableRows', 'standings', 'table'], true)
                && Arr::isList($child)
                && isset($child[0])
                && is_array($child[0])
                && $this->looksLikeStandingRow($child[0])) {
                return $child;
            }

            $rows = $this->findRowsRecursively($child);
            if ($rows !== []) {
                return $rows;
            }
        }

        return [];
    }

    /** @param array<string, mixed> $row */
    private function looksLikeStandingRow(array $row): bool
    {
        return isset($row['position'], $row['team'])
            || isset($row['rank'], $row['team'])
            || isset($row['position'], $row['teamName'])
            || isset($row['rank'], $row['teamName'])
            || isset($row['position'], $row['competitor'])
            || isset($row['rank'], $row['competitor'])
            || isset($row['number'], $row['competitor']);
    }

    /** @param array<string, mixed> $row */
    private function normalizeRow(array $row): array
    {
        $team = is_array($row['team'] ?? null)
            ? $row['team']
            : (is_array($row['competitor'] ?? null) ? $row['competitor'] : []);
        $stats = is_array($row['stats'] ?? null) ? $row['stats'] : [];

        $played = $this->number($row, $stats, ['played', 'games', 'matches', 'gamesPlayed']);
        $wins = $this->number($row, $stats, ['wins', 'won']);
        $draws = $this->number($row, $stats, ['draws', 'ties']);
        $losses = $this->number($row, $stats, ['losses', 'lost']);
        $goalsFor = $this->number($row, $stats, ['goalsFor', 'goals_for', 'scored']);
        $goalsAgainst = $this->number($row, $stats, ['goalsAgainst', 'goals_against', 'conceded']);
        $goalDifference = $this->number($row, $stats, ['goalDifference', 'goal_diff', 'difference']);

        return [
            'position' => $this->number($row, [], ['position', 'rank', 'place', 'number']),
            'team' => [
                'id' => data_get($team, 'id') ?? data_get($row, 'teamId'),
                'name' => data_get($team, 'name') ?? data_get($team, 'displayName')
                    ?? data_get($row, 'teamName') ?? data_get($row, 'name'),
                'logo' => data_get($team, 'logo') ?? data_get($team, 'image') ?? data_get($team, 'imageUrl')
                    ?? data_get($row, 'teamLogo') ?? data_get($row, 'logo'),
            ],
            'played' => $played,
            'wins' => $wins,
            'draws' => $draws,
            'losses' => $losses,
            'goals_for' => $goalsFor,
            'goals_against' => $goalsAgainst,
            'goal_difference' => $goalDifference ?? (($goalsFor !== null && $goalsAgainst !== null) ? $goalsFor - $goalsAgainst : null),
            'points' => $this->number($row, $stats, ['points', 'pts']),
            'form' => data_get($row, 'form') ?? data_get($stats, 'form'),
        ];
    }

    /** @param array<string, mixed> $first @param array<string, mixed> $second @param array<int, string> $keys */
    private function number(array $first, array $second, array $keys): ?int
    {
        foreach ($keys as $key) {
            $value = data_get($first, $key) ?? data_get($second, $key);
            if (is_numeric($value)) {
                return (int) $value;
            }
        }

        return null;
    }

    /** @param array<string, mixed> $source @param array<int, string> $paths @return array<string, mixed> */
    private function firstArray(array $source, array $paths): array
    {
        foreach ($paths as $path) {
            $value = data_get($source, $path);
            if (is_array($value)) {
                return $value;
            }
        }

        return [];
    }
}

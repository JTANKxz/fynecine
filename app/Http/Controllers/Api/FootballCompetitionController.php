<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Championship;
use App\Services\Sports365Service;
use Illuminate\Http\JsonResponse;

class FootballCompetitionController extends Controller
{
    public function index(Sports365Service $sports): JsonResponse
    {
        $competitions = Championship::query()
            ->where('is_sports_enabled', true)
            ->where('external_provider', '365scores')
            ->whereNotNull('external_id')
            ->orderByDesc('is_featured')->orderBy('display_order')->orderBy('name')->get();

        return response()->json([
            'provider' => '365scores',
            'competitions' => $competitions->map(fn (Championship $item) => $this->competition($item))->values(),
            'featured_ids' => $competitions->where('is_featured', true)->pluck('id')->values(),
        ])->header('Cache-Control', 'public, max-age=300');
    }

    public function show(Championship $championship, Sports365Service $sports): JsonResponse
    {
        abort_unless($championship->is_sports_enabled && $championship->external_provider === '365scores' && $championship->external_id, 404);

        try {
            // Não bloqueia a tabela por metadados antigos salvos no painel.
            $standing = $sports->standings($championship);
            return response()->json([
                'provider' => '365scores',
                'competition' => $this->competition($championship),
                'table' => collect(data_get($standing, 'rows', []))->map(fn ($row) => $this->mapStanding($row))->values(),
                'upcoming_games' => $sports->upcomingGames($championship)->map(fn ($game) => $this->mapGame($game))->values(),
                'recent_games' => $sports->recentGames($championship)->map(fn ($game) => $this->mapGame($game))->values(),
            ])->header('Cache-Control', 'public, max-age=300');
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Não foi possível carregar este campeonato agora.'], 503);
        }
    }

    private function competition(Championship $item): array
    {
        return ['id' => $item->id, 'source_id' => (int) $item->external_id, 'name' => $item->name, 'season' => $item->current_season_name, 'logo' => $item->image_url, 'color' => $item->provider_color, 'is_featured' => (bool) $item->is_featured, 'has_standings' => (bool) $item->has_standings];
    }

    private function mapStanding(array $row): array
    {
        return array_merge($row, ['team' => $this->mapTeam($row['team'] ?? [])]);
    }

    private function mapGame(array $game): array
    {
        return array_merge($game, ['home_team' => $this->mapTeam($game['home_team'] ?? []), 'away_team' => $this->mapTeam($game['away_team'] ?? [])]);
    }

    private function mapTeam(array $team): array
    {
        return ['source_id' => data_get($team, 'source_id'), 'name' => data_get($team, 'name'), 'logo' => data_get($team, 'logo') ?? data_get($team, 'logo_url')];
    }
}

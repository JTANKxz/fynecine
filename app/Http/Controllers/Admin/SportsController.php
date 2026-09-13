<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Championship;
use App\Models\Team;
use App\Services\Sports365Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SportsController extends Controller
{
    public function index(Request $request, Sports365Service $sports): View
    {
        $championships = Championship::orderByDesc('is_sports_enabled')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        $selected = $championships->firstWhere('id', (int) $request->integer('championship'))
            ?? $championships->firstWhere('is_sports_enabled', true);
        $standings = null;
        $games = collect();
        $sourceError = null;

        if ($selected?->is_sports_enabled && $selected->external_provider === '365scores' && $selected->external_id) {
            try {
                if ($selected->has_current_stage_standings !== false) {
                    $standings = $sports->standings($selected);
                }
                $games = $sports->upcomingGames($selected);
            } catch (\Throwable $exception) {
                report($exception);
                $sourceError = 'Não foi possível consultar a fonte esportiva agora. Tente novamente em alguns minutos.';
            }
        }

        $teams = Team::query()
            ->where('external_provider', '365scores')
            ->whereNotNull('external_id')
            ->orderBy('name')
            ->get();
        $selectedTeam = $teams->firstWhere('id', (int) $request->integer('team'));
        $teamForm = collect();
        $teamGames = collect();
        $catalog = collect();
        $catalogCountries = collect();
        $countries = collect();
        $catalogCountryId = $request->integer('catalog_country') ?: null;

        if ($selectedTeam) {
            try {
                $teamForm = $sports->teamRecentForm($selectedTeam);
                $teamGames = $sports->teamUpcomingGames($selectedTeam);
            } catch (\Throwable $exception) {
                report($exception);
                $sourceError ??= 'Não foi possível consultar os dados recentes do time agora.';
            }
        }

        if ($request->boolean('catalog')) {
            try {
                $catalogCountries = $sports->featuredCountries();
                $catalog = $sports->featuredCompetitions($catalogCountryId);
            } catch (\Throwable $exception) {
                report($exception);
                $sourceError ??= 'Não foi possível buscar os campeonatos em destaque agora.';
            }
        }

        try {
            $countries = $sports->countries();
        } catch (\Throwable $exception) {
            report($exception);
        }

        return view('admin.sports.index', compact(
            'championships', 'selected', 'standings', 'games', 'sourceError',
            'teams', 'selectedTeam', 'teamForm', 'teamGames', 'catalog',
            'catalogCountries', 'catalogCountryId', 'countries'
        ));
    }

    public function configure(Request $request, Championship $championship): RedirectResponse
    {
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:100'],
            'sport_id' => ['nullable', 'integer', 'min:1'],
            'country_id' => ['nullable', 'integer', 'min:1'],
            'display_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'image_url' => ['nullable', 'string', 'max:500'],
            'image_upload' => ['nullable', 'image', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $enabled = $request->boolean('is_sports_enabled');
        $imageUrl = $request->boolean('remove_image') ? null : ($data['image_url'] ?? $championship->image_url);
        if ($request->hasFile('image_upload')) {
            $imageUrl = '/storage/'.$request->file('image_upload')->store('championships', 'public');
        }
        $championship->update([
            'external_provider' => $enabled ? '365scores' : null,
            'external_id' => $enabled ? ($data['external_id'] ?? null) : null,
            'sport_id' => $enabled ? ($data['sport_id'] ?? 1) : null,
            'country_id' => $enabled ? ($data['country_id'] ?? null) : null,
            'is_sports_enabled' => $enabled,
            'auto_sync' => $enabled && $request->boolean('auto_sync'),
            'display_order' => $data['display_order'] ?? 0,
            'image_url' => $imageUrl,
        ]);

        return back()->with('success', 'Configuração esportiva atualizada.');
    }

    public function sync(Championship $championship, Sports365Service $sports): RedirectResponse
    {
        if (! $championship->is_sports_enabled || $championship->external_provider !== '365scores' || ! $championship->external_id) {
            return back()->with('error', 'Ative a integração e informe o ID do campeonato no 365Scores antes de sincronizar.');
        }

        try {
            $count = $sports->syncUpcomingGames($championship);
            return back()->with('success', "{$count} próximo(s) jogo(s) sincronizado(s) em Eventos Ao Vivo.");
        } catch (\Throwable $exception) {
            report($exception);
            return back()->with('error', 'A sincronização falhou. Confira o ID da competição e tente novamente.');
        }
    }

    public function importFeatured(Request $request, Sports365Service $sports): RedirectResponse
    {
        $ids = collect($request->input('competitions', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Selecione pelo menos um campeonato para importar.');
        }

        try {
            $available = $sports->featuredCompetitions()->keyBy('source_id');
            $count = 0;

            foreach ($ids as $id) {
                $item = $available->get($id);
                if (! $item) {
                    continue;
                }

                $championship = Championship::where('external_provider', '365scores')
                    ->where('external_id', (string) $item['source_id'])
                    ->first();

                $championship ??= $this->findExistingChampionship((int) $item['source_id'], $item['name']);
                $championship ??= new Championship();
                $championship->fill([
                    'name' => $championship->name ?: $item['name'],
                    'external_provider' => '365scores',
                    'external_id' => (string) $item['source_id'],
                    'sport_id' => $item['sport_id'],
                    'country_id' => $item['country_id'],
                    'current_season_num' => $item['current_season_num'],
                    'current_season_name' => $item['current_season_name'],
                    'current_stage_num' => $item['current_stage_num'],
                    'current_stage_name' => $item['current_stage_name'],
                    'stage_type' => $item['stage_type'],
                    'has_standings' => true,
                    'has_live_standings' => $item['has_live_standings'],
                    'has_current_stage_standings' => $item['has_current_stage_standings'],
                    'has_brackets' => $item['has_brackets'],
                    'has_stats' => $item['has_stats'],
                    'provider_color' => $item['color'],
                    // Nunca substitui uma capa escolhida manualmente no painel.
                    'image_url' => $championship->image_url ?: $item['logo_url'],
                    'is_sports_enabled' => true,
                    'auto_sync' => true,
                    'display_order' => $championship->display_order ?: 100 + $count,
                ]);
                $championship->save();
                $count++;
            }

            return redirect()->route('admin.sports.index')->with('success', "{$count} campeonato(s) importado(s) e ativado(s).");
        } catch (\Throwable $exception) {
            report($exception);
            return back()->with('error', 'A importação falhou. Tente novamente em alguns minutos.');
        }
    }

    private function findExistingChampionship(int $externalId, string $sourceName): ?Championship
    {
        $knownNames = [
            113 => 'Brasileirão Série A',
            116 => 'Brasileirão Série B',
            7 => 'Premier League',
            11 => 'La LIga',
            17 => 'Campeonato Italiano',
            25 => 'Bundesliga',
        ];
        $candidates = array_filter([$knownNames[$externalId] ?? null, $sourceName]);

        return Championship::get()->first(function (Championship $championship) use ($candidates) {
            $name = Str::of($championship->name)->ascii()->lower()->trim()->value();
            return collect($candidates)->contains(fn ($candidate) => $name === Str::of($candidate)->ascii()->lower()->trim()->value());
        });
    }
}

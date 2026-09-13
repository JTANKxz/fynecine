@extends('layouts.admin')

@section('title', 'Central de Esportes')

@section('content')
<section class="max-w-7xl space-y-7">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[.2em] text-netflix font-bold">365Scores · Agenda e classificação</p>
            <h1 class="text-2xl font-black text-white mt-1">Central de Esportes</h1>
            <p class="text-sm text-neutral-400 mt-1">Ative campeonatos, consulte a tabela e sincronize automaticamente os próximos jogos com Eventos Ao Vivo.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.sports.index', ['catalog' => 1]) }}" class="rounded-xl border border-neutral-700 bg-neutral-900 px-4 py-3 text-sm font-bold text-white hover:border-netflix transition"><i class="fa-solid fa-magnifying-glass mr-2"></i>Buscar campeonatos</a>
            @if($selected)
                <form method="POST" action="{{ route('admin.sports.sync', $selected) }}">
                    @csrf
                    <button class="rounded-xl bg-netflix px-5 py-3 text-sm font-bold text-white hover:brightness-110 transition">
                        <i class="fa-solid fa-rotate mr-2"></i>Sincronizar {{ $selected->name }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">{{ session('success') }}</div>
    @endif
    @if(session('error') || $sourceError)
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">{{ session('error') ?: $sourceError }}</div>
    @endif

    @if($catalog->isNotEmpty())
        <form method="POST" action="{{ route('admin.sports.import-featured') }}" class="rounded-2xl border border-netflix/30 bg-neutral-900 p-5">
            @csrf
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div><h2 class="font-bold text-white">Campeonatos em destaque</h2><p class="mt-1 text-xs text-neutral-400">Fonte: 365Scores. Selecione os campeonatos que deseja ativar e sincronizar.</p></div>
                <button class="rounded-xl bg-netflix px-4 py-2.5 text-xs font-black text-white">IMPORTAR SELECIONADOS</button>
            </div>
            <div class="mt-4 flex flex-wrap gap-2 border-y border-neutral-800 py-3">
                <a href="{{ route('admin.sports.index', ['catalog' => 1]) }}" class="rounded-full px-3 py-1 text-xs font-bold {{ !$catalogCountryId ? 'bg-netflix text-white' : 'bg-neutral-800 text-neutral-400 hover:text-white' }}">Todos</a>
                @foreach($catalogCountries as $country)
                    <a href="{{ route('admin.sports.index', ['catalog' => 1, 'catalog_country' => $country['id']]) }}" class="rounded-full px-3 py-1 text-xs font-bold {{ $catalogCountryId === $country['id'] ? 'bg-netflix text-white' : 'bg-neutral-800 text-neutral-400 hover:text-white' }}">{{ $country['name'] }}</a>
                @endforeach
            </div>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($catalog as $item)
                    @php $configured = $championships->first(fn ($championship) => $championship->external_provider === '365scores' && (string) $championship->external_id === (string) $item['source_id']); @endphp
                    <label class="flex cursor-pointer gap-3 rounded-xl border border-neutral-800 bg-black/30 p-3 hover:border-neutral-600 transition">
                        <input class="mt-1 accent-netflix" type="checkbox" name="competitions[]" value="{{ $item['source_id'] }}" @checked($configured)>
                        @if($item['logo_url'])
                            <img src="{{ $item['logo_url'] }}" alt="" class="mt-0.5 h-11 w-11 shrink-0 object-contain" loading="lazy">
                        @endif
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-bold text-white">{{ $item['name'] }}</span>
                            <span class="mt-1 block text-xs text-neutral-500">{{ $item['country_name'] ?: 'Internacional' }} · ID {{ $item['source_id'] }}</span>
                            @if($item['current_season_name'] || $item['current_stage_name'])
                                <span class="mt-1 block text-[10px] text-neutral-500">{{ $item['current_season_name'] }} · {{ $item['current_stage_name'] ?: 'Fase atual' }}</span>
                            @endif
                            <span class="mt-2 inline-flex rounded-full bg-neutral-800 px-2 py-0.5 text-[10px] font-bold {{ $item['has_live_standings'] ? 'text-emerald-400' : 'text-neutral-400' }}">{{ $item['has_live_standings'] ? 'TABELA AO VIVO' : 'CLASSIFICAÇÃO' }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </form>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <div class="rounded-2xl border border-neutral-800 bg-neutral-900 overflow-hidden">
                <div class="border-b border-neutral-800 p-5 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="font-bold text-white">Campeonatos configurados</h2>
                        <p class="text-xs text-neutral-500 mt-1">Cada competição precisa do ID público do 365Scores.</p>
                    </div>
                    <a class="text-xs font-bold text-netflix hover:text-white" href="{{ route('admin.championships.index') }}">GERENCIAR LISTA</a>
                </div>
                <div class="divide-y divide-neutral-800">
                    @forelse($championships as $championship)
                        <div class="p-4 {{ $selected?->id === $championship->id ? 'bg-neutral-800/40' : '' }}">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <a href="{{ route('admin.sports.index', ['championship' => $championship->id]) }}" class="flex min-w-0 items-center gap-3">
                                    @if($championship->image_url)
                                        <img src="{{ $championship->image_url }}" alt="" class="h-9 w-9 shrink-0 object-contain" loading="lazy">
                                    @endif
                                    <div class="min-w-0">
                                    <div class="truncate font-bold text-white">{{ $championship->name }}</div>
                                    <div class="mt-1 text-xs text-neutral-500">
                                        @if($championship->is_sports_enabled)
                                            365Scores #{{ $championship->external_id ?: 'sem ID' }}
                                            @if($championship->last_synced_at) · Atualizado {{ $championship->last_synced_at->diffForHumans() }} @endif
                                        @else
                                            Não integrado
                                        @endif
                                    </div>
                                    </div>
                                </a>
                                <div class="flex items-center gap-2 text-[10px] font-black uppercase">
                                    @if($championship->is_sports_enabled)
                                        <span class="rounded-full bg-emerald-500/10 px-2 py-1 text-emerald-400">Ativo</span>
                                        @if($championship->auto_sync)<span class="rounded-full bg-blue-500/10 px-2 py-1 text-blue-300">Auto</span>@endif
                                    @else
                                        <span class="rounded-full bg-neutral-800 px-2 py-1 text-neutral-500">Inativo</span>
                                    @endif
                                </div>
                            </div>
                            <details class="mt-3 rounded-xl border border-neutral-800 bg-black/20">
                                <summary class="cursor-pointer px-3 py-2 text-xs font-bold text-neutral-300">Configurar integração</summary>
                                <form method="POST" action="{{ route('admin.sports.configure', $championship) }}" enctype="multipart/form-data" class="grid gap-3 border-t border-neutral-800 p-3 sm:grid-cols-2">
                                    @csrf @method('PUT')
                                    <label class="text-xs text-neutral-400 sm:col-span-2 flex items-center gap-2">
                                        <input type="checkbox" name="is_sports_enabled" value="1" @checked($championship->is_sports_enabled) class="accent-netflix"> Ativar no módulo esportivo
                                    </label>
                                    <label class="text-xs text-neutral-400 sm:col-span-2 flex items-center gap-2">
                                        <input type="checkbox" name="is_featured" value="1" @checked($championship->is_featured) class="accent-netflix"> Exibir em destaque na tela inicial de Esportes
                                    </label>
                                    <label class="text-xs text-neutral-400">ID da competição 365Scores
                                        <input name="external_id" value="{{ $championship->external_id }}" inputmode="numeric" placeholder="Ex.: 113" class="mt-1 w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm text-white">
                                    </label>
                                    <label class="text-xs text-neutral-400">Ordem de exibição
                                        <input name="display_order" value="{{ $championship->display_order }}" type="number" min="0" class="mt-1 w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm text-white">
                                    </label>
                                    <label class="text-xs text-neutral-400">ID do esporte
                                        <input name="sport_id" value="{{ $championship->sport_id ?: 1 }}" type="number" min="1" class="mt-1 w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm text-white">
                                    </label>
                                    <label class="text-xs text-neutral-400">ID do país (opcional)
                                        <input name="country_id" value="{{ $championship->country_id }}" type="number" min="1" list="sportsCountries" class="mt-1 w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm text-white">
                                    </label>
                                    <div class="sm:col-span-2 rounded-xl border border-neutral-800 bg-neutral-950/50 p-3">
                                        <div class="mb-3 flex items-center gap-3">
                                            @if($championship->image_url)
                                                <img src="{{ $championship->image_url }}" alt="Capa atual" class="h-14 w-14 rounded-lg object-contain bg-neutral-900 p-1">
                                            @else
                                                <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-neutral-900 text-neutral-600"><i class="fa-solid fa-trophy"></i></div>
                                            @endif
                                            <div><p class="text-xs font-bold text-white">Capa do campeonato</p><p class="mt-1 text-[11px] text-neutral-500">A imagem manual tem prioridade sobre a capa da fonte.</p></div>
                                        </div>
                                        <div class="grid gap-3 sm:grid-cols-2">
                                            <label class="text-xs text-neutral-400">URL da imagem
                                                <input name="image_url" value="{{ $championship->image_url }}" type="url" placeholder="https://..." class="mt-1 w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm text-white">
                                            </label>
                                            <label class="text-xs text-neutral-400">Enviar imagem (até 4 MB)
                                                <input name="image_upload" type="file" accept="image/*" class="mt-1 block w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-xs text-neutral-300 file:mr-3 file:border-0 file:bg-neutral-800 file:px-2 file:py-1 file:text-xs file:font-bold file:text-white">
                                            </label>
                                        </div>
                                        @if($championship->image_url)
                                            <label class="mt-3 flex items-center gap-2 text-xs text-red-300"><input type="checkbox" name="remove_image" value="1" class="accent-red-500"> Remover capa personalizada</label>
                                        @endif
                                    </div>
                                    <label class="text-xs text-neutral-400 sm:col-span-2 flex items-center gap-2">
                                        <input type="checkbox" name="auto_sync" value="1" @checked($championship->auto_sync) class="accent-netflix"> Sincronizar próximos jogos automaticamente a cada 30 minutos
                                    </label>
                                    <button class="sm:col-span-2 rounded-lg bg-neutral-800 px-4 py-2 text-xs font-bold text-white hover:bg-neutral-700">Salvar configuração</button>
                                </form>
                            </details>
                        </div>
                    @empty
                        <div class="p-10 text-center text-sm text-neutral-500">Cadastre um campeonato antes de configurar a fonte esportiva.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="rounded-2xl border border-neutral-800 bg-neutral-900 p-5">
                <h2 class="font-bold text-white">{{ $selected?->name ?: 'Selecione um campeonato' }}</h2>
                @if($standings)
                    <p class="mt-1 text-xs text-neutral-500">Classificação atual · {{ $standings['rows']->count() }} times</p>
                    <div class="mt-4 max-h-[430px] overflow-y-auto divide-y divide-neutral-800">
                        @foreach($standings['rows'] as $row)
                            <div class="flex items-center gap-2 py-2 text-xs">
                                <span class="w-5 text-center font-black text-netflix">{{ $row['position'] }}</span>
                                @if($row['team']['logo_url'])<img src="{{ $row['team']['logo_url'] }}" class="h-6 w-6 object-contain" alt="">@endif
                                <span class="min-w-0 flex-1 truncate text-white font-semibold">{{ $row['team']['name'] }}</span>
                                <span class="font-black text-white">{{ $row['points'] }} P</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-3 text-sm text-neutral-500">Ative uma competição e informe o ID do 365Scores para consultar a tabela.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-neutral-800 bg-neutral-900 p-5">
                <h2 class="font-bold text-white">Próximos jogos</h2>
                <div class="mt-3 space-y-3">
                    @forelse($games as $game)
                        <div class="rounded-xl bg-black/30 p-3 text-xs">
                            <div class="text-neutral-500">{{ \Carbon\Carbon::parse($game['starts_at'])->format('d/m · H:i') }} @if($game['round']) · Rodada {{ $game['round'] }} @endif</div>
                            <div class="mt-1 font-bold text-white">{{ $game['home_team']['name'] }} <span class="text-neutral-500">x</span> {{ $game['away_team']['name'] }}</div>
                            @if($game['venue'])<div class="mt-1 truncate text-neutral-500">{{ $game['venue'] }}</div>@endif
                        </div>
                    @empty
                        <p class="text-sm text-neutral-500">Nenhum jogo futuro retornado.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-neutral-800 bg-neutral-900 p-5">
                <form method="GET" action="{{ route('admin.sports.index') }}" class="flex items-end gap-2">
                    @if($selected)<input type="hidden" name="championship" value="{{ $selected->id }}">@endif
                    <label class="min-w-0 flex-1 text-xs font-bold text-neutral-400">Forma e agenda do time
                        <select name="team" onchange="this.form.submit()" class="mt-1 w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-sm text-white">
                            <option value="">Selecione um time</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}" @selected($selectedTeam?->id === $team->id)>{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </label>
                </form>
                @if($selectedTeam)
                    <div class="mt-4 flex items-center gap-3">
                        @if($selectedTeam->image_url)<img class="h-10 w-10 object-contain" src="{{ $selectedTeam->image_url }}" alt="">@endif
                        <div><div class="font-bold text-white">{{ $selectedTeam->name }}</div><div class="text-xs text-neutral-500">Últimos 5 jogos</div></div>
                    </div>
                    <div class="mt-3 flex gap-2">
                        @forelse($teamForm as $game)
                            @php $colors = ['W' => 'bg-emerald-500/15 text-emerald-400', 'D' => 'bg-yellow-500/15 text-yellow-300', 'L' => 'bg-red-500/15 text-red-400']; @endphp
                            <span title="{{ $game['opponent'] }} · {{ $game['team_score'] }} x {{ $game['opponent_score'] }}" class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-black {{ $colors[$game['result']] ?? 'bg-neutral-800 text-neutral-400' }}">{{ $game['result'] }}</span>
                        @empty
                            <span class="text-xs text-neutral-500">Sem resultados disponíveis.</span>
                        @endforelse
                    </div>
                    @if($teamGames->isNotEmpty())
                        <div class="mt-4 border-t border-neutral-800 pt-3 text-xs font-bold text-neutral-400">PRÓXIMOS JOGOS</div>
                        <div class="mt-2 space-y-2">
                            @foreach($teamGames->take(4) as $game)
                                <div class="text-xs text-neutral-300">{{ \Carbon\Carbon::parse($game['starts_at'])->format('d/m H:i') }} · {{ $game['home_team']['name'] }} x {{ $game['away_team']['name'] }}</div>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </aside>
    </div>
</section>
<datalist id="sportsCountries">
    @foreach($countries as $country)
        <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
    @endforeach
</datalist>
@endsection

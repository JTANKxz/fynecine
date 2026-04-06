@extends('layouts.admin')

@section('title', 'Editar Evento Ao Vivo')

@section('content')
<section class="max-w-4xl">
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('admin.events.index') }}" class="w-10 h-10 bg-neutral-900 border border-neutral-800 rounded-full flex items-center justify-center hover:bg-neutral-800 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-white">Editar: {{ $event->title }}</h2>
            <p class="text-sm text-neutral-500">Altere os detalhes da transmissão abaixo.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-900/20 border border-red-600 text-red-400 px-4 py-3 rounded text-sm">
            <ul class="list-disc ml-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-6 md:p-8 shadow-2xl">
        <form action="{{ route('admin.events.update', $event->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Campeonato --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Campeonato (Opcional)</label>
                    <select name="championship_id" onchange="updateTitleFromChamp(this)"
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition h-[48px]">
                        <option value="">Selecione um campeonato...</option>
                        @foreach($championships as $champ)
                            <option value="{{ $champ->id }}" {{ $event->championship_id == $champ->id ? 'selected' : '' }}>{{ $champ->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Título --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Título do Evento</label>
                    <input type="text" name="title" id="event_title" value="{{ old('title', $event->title) }}" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                {{-- Imagem --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">URL da Capa/Banner (Opcional)</label>
                    <input type="url" name="image_url" value="{{ old('image_url', $event->image_url) }}" placeholder="https://..."
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition text-xs font-mono">
                </div>
            </div>

            <div class="p-4 bg-neutral-800/30 rounded-xl border border-neutral-800 space-y-4">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fa-solid fa-shield-halved text-netflix"></i>
                    <span class="text-xs font-bold text-neutral-400 uppercase">Times (Opcional)</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Time Home --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Time da Casa</label>
                        <input type="text" name="home_team" value="{{ old('home_team', $event->home_team) }}" placeholder="Digite o nome (ex: Real Madrid)"
                            class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">
                        <input type="hidden" name="home_team_id" id="home_team_id" value="{{ old('home_team_id', $event->home_team_id) }}">
                        <div class="relative">
                            <input type="text" id="search_home_team" placeholder="🔍 Buscar time cadastrado..."
                                class="w-full bg-neutral-900 border border-neutral-700 rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-netflix transition"
                                oninput="searchTeam('home', this.value)">
                            <div id="home_team_results" class="hidden absolute z-10 w-full bg-neutral-900 border border-neutral-700 rounded-lg mt-1 max-h-40 overflow-y-auto shadow-xl text-xs"></div>
                        </div>
                        <div id="home_team_preview" class="{{ $event->homeTeam ? '' : 'hidden' }} flex items-center gap-2 bg-neutral-900 rounded-lg p-2 border border-netflix/30">
                            <img id="home_team_img" src="{{ $event->homeTeam->image_url ?? '' }}" class="w-8 h-8 object-contain rounded">
                            <span id="home_team_name" class="text-xs font-bold text-white flex-1">{{ $event->homeTeam->name ?? '' }}</span>
                            <button type="button" onclick="clearTeam('home')" class="text-red-400 text-xs hover:text-red-300"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>

                    {{-- Time Away --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Time Visitante</label>
                        <input type="text" name="away_team" value="{{ old('away_team', $event->away_team) }}" placeholder="Digite o nome (ex: Barcelona)"
                            class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">
                        <input type="hidden" name="away_team_id" id="away_team_id" value="{{ old('away_team_id', $event->away_team_id) }}">
                        <div class="relative">
                            <input type="text" id="search_away_team" placeholder="🔍 Buscar time cadastrado..."
                                class="w-full bg-neutral-900 border border-neutral-700 rounded-lg px-3 py-2 text-white text-xs focus:outline-none focus:border-netflix transition"
                                oninput="searchTeam('away', this.value)">
                            <div id="away_team_results" class="hidden absolute z-10 w-full bg-neutral-900 border border-neutral-700 rounded-lg mt-1 max-h-40 overflow-y-auto shadow-xl text-xs"></div>
                        </div>
                        <div id="away_team_preview" class="{{ $event->awayTeam ? '' : 'hidden' }} flex items-center gap-2 bg-neutral-900 rounded-lg p-2 border border-netflix/30">
                            <img id="away_team_img" src="{{ $event->awayTeam->image_url ?? '' }}" class="w-8 h-8 object-contain rounded">
                            <span id="away_team_name" class="text-xs font-bold text-white flex-1">{{ $event->awayTeam->name ?? '' }}</span>
                            <button type="button" onclick="clearTeam('away')" class="text-red-400 text-xs hover:text-red-300"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Início --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Horário de Início (Fuso SP)</label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time', $event->start_time->format('Y-m-d\TH:i')) }}" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition text-xs">
                </div>

                {{-- Fim --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Horário de Término (Fuso SP)</label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time', $event->end_time->format('Y-m-d\TH:i')) }}" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition text-xs">
                </div>
            </div>

            {{-- Descrição --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Descrição (Opcional)</label>
                <textarea name="description" rows="3" placeholder="Detalhes sobre a transmissão..."
                    class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">{{ old('description', $event->description) }}</textarea>
            </div>

            <div class="border-t border-neutral-800 pt-6 flex flex-col md:flex-row gap-8 items-center justify-between">
                {{-- Status --}}
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="is_active" id="is_active" {{ $event->is_active ? 'checked' : '' }} value="1"
                        class="w-5 h-5 accent-netflix rounded bg-black border-neutral-800">
                    <span class="text-white font-bold text-sm group-hover:text-netflix transition text-nowrap">EVENTO ATIVO</span>
                </label>

                <button type="submit" class="w-full md:w-auto bg-netflix hover:bg-red-700 text-white font-black px-10 py-4 rounded-xl shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-save mr-1"></i> ATUALIZAR EVENTO
                </button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
function updateTitleFromChamp(select) {
    const titleInput = document.getElementById('event_title');
    const selectedText = select.options[select.selectedIndex].text;
    if (select.value) {
        titleInput.value = selectedText;
    }
}

let searchTimeout;
function searchTeam(side, query) {
    clearTimeout(searchTimeout);
    const results = document.getElementById(side + '_team_results');
    if (query.length < 2) { results.classList.add('hidden'); return; }
    
    searchTimeout = setTimeout(() => {
        fetch('{{ route("admin.teams.search") }}?query=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(teams => {
                if (teams.length === 0) {
                    results.innerHTML = '<div class="p-3 text-neutral-500 text-xs text-center">Nenhum time encontrado</div>';
                } else {
                    results.innerHTML = teams.map(t => `
                        <div onclick="selectTeam('${side}', ${t.id}, '${t.name.replace(/'/g, "\\'")}', '${t.image_url || ''}')" 
                             class="flex items-center gap-2 p-2 hover:bg-neutral-800 cursor-pointer transition">
                            ${t.image_url ? `<img src="${t.image_url}" class="w-6 h-6 object-contain rounded">` : '<i class="fa-solid fa-shield-halved text-neutral-600 w-6 text-center"></i>'}
                            <span class="text-xs text-white">${t.name}</span>
                        </div>
                    `).join('');
                }
                results.classList.remove('hidden');
            });
    }, 300);
}

function selectTeam(side, id, name, image) {
    document.getElementById(side + '_team_id').value = id;
    document.querySelector(`[name="${side}_team"]`).value = name;
    document.getElementById(side + '_team_results').classList.add('hidden');
    document.getElementById('search_' + side + '_team').value = '';
    
    // Show preview
    const preview = document.getElementById(side + '_team_preview');
    document.getElementById(side + '_team_name').textContent = name;
    if (image) document.getElementById(side + '_team_img').src = image;
    preview.classList.remove('hidden');
}

function clearTeam(side) {
    document.getElementById(side + '_team_id').value = '';
    document.getElementById(side + '_team_preview').classList.add('hidden');
}

document.addEventListener('click', (e) => {
    ['home', 'away'].forEach(side => {
        const results = document.getElementById(side + '_team_results');
        if (results && !results.contains(e.target) && e.target.id !== 'search_' + side + '_team') {
            results.classList.add('hidden');
        }
    });
});
</script>
@endpush

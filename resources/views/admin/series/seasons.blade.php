@extends('layouts.admin')

@section('title', 'Temporadas')

@section('content')

    <section>

        <div class="flex justify-between items-center mb-4 gap-3">
            <h2 class="text-xl font-bold">
                Temporadas - {{ $serie->name }}
            </h2>

            <div class="flex gap-3">
                <button onclick="openSyncSeasonsModal()" 
                    class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-sync"></i> Sincronizar TMDB
                </button>

                <a href="{{ route('admin.series.index') }}" class="bg-neutral-700 px-4 py-2 rounded hover:bg-neutral-600 text-sm">
                    Voltar
                </a>
            </div>
        </div>

        <div class="bg-neutral-900 rounded-lg overflow-hidden">

            <div class="table-container">

                <table class="w-full">

                <thead class="bg-neutral-800">

                    <tr>

                        <th class="text-left p-4">ID</th>

                        <th class="text-left p-4">Temporada</th>

                        <th class="text-left p-4">TMDB ID</th>

                        <th class="text-left p-4">Status</th>

                        <th class="text-left p-4">Ações</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($seasons as $season)
                        <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">

                            <td class="p-4">{{ $season->id }}</td>

                            <td class="p-4">
                                Temporada {{ $season->season_number }}
                            </td>

                            <td class="p-4">
                                {{ $season->tmdb_id }}
                            </td>

                            <td class="p-4">

                                @if ($season->status == 'active')
                                    <span class="text-green-400">
                                        Ativo
                                    </span>
                                @else
                                    <span class="text-red-400">
                                        Inativo
                                    </span>
                                @endif

                            </td>

                            <td class="p-4 flex gap-3">

                                <a href="{{ route('admin.series.episodes', $season->id) }}"
                                    class="text-green-500 hover:text-green-400">

                                    <i class="fa-solid fa-film"></i>

                                </a>

                                {{-- editar --}}
                                <button
                                    onclick="openSeasonModal({{ $season->id }}, '{{ $season->status }}')"
                                    class="text-blue-500 hover:text-blue-400" title="Editar">

                                    <i class="fa-solid fa-edit"></i>

                                </button>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="text-center p-4">
                                Nenhuma temporada encontrada
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
            </div>

            <div class="p-4">

                {{ $seasons->links() }}

            </div>

        </div>

        <div id="seasonModal" style="display:none" class="fixed inset-0 bg-black/60 flex items-center justify-center">

            <div class="bg-neutral-900 p-6 rounded-lg w-full max-w-md">

                <h2 class="text-lg font-bold mb-4">Editar Temporada</h2>

                <form id="seasonForm" method="POST">

                    @csrf
                    @method('PUT')

                    <div class="space-y-4">

                        <div>
                            <label class="text-sm text-neutral-400">
                                Status
                            </label>

                            <select name="status" id="seasonStatus" class="w-full p-2 bg-neutral-800 rounded mt-1">

                                <option value="active">Ativo</option>
                                <option value="inactive">Inativo</option>

                            </select>
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 mt-6">

                        <button type="button" onclick="closeSeasonModal()" class="bg-neutral-700 px-4 py-2 rounded">

                            Cancelar

                        </button>

                        <button class="bg-netflix px-4 py-2 rounded hover:bg-red-700">

                            Salvar

                        </button>

                    </div>

                </form>

            </div>

        </div>

        <div id="syncModal" style="display:none" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50">
            <div class="bg-neutral-900 rounded-lg w-full max-w-2xl max-h-[80vh] flex flex-col">
                <div class="p-6 border-b border-neutral-800 flex justify-between items-center">
                    <h2 class="text-xl font-bold">Sincronizar Temporadas</h2>
                    <button onclick="closeSyncModal()" class="text-neutral-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>
                
                <div id="syncModalContent" class="p-6 overflow-y-auto flex-1">
                    <div class="flex justify-center p-8">
                        <i class="fa-solid fa-circle-notch fa-spin text-4xl text-blue-500"></i>
                    </div>
                </div>

                <div class="p-6 border-t border-neutral-800 flex justify-end gap-3">
                    <button onclick="closeSyncModal()" class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600">
                        Cancelar
                    </button>
                    <button id="btnConfirmSync" onclick="confirmSyncSeasons()" class="bg-blue-600 px-6 py-2 rounded hover:bg-blue-700 hidden">
                        Sincronizar Selecionadas
                    </button>
                </div>
            </div>
        </div>

        <script>
            let serieTmdbId = "{{ $serie->tmdb_id }}";
            let serieId = "{{ $serie->id }}";

            function openSyncSeasonsModal() {
                const modal = document.getElementById('syncModal');
                const content = document.getElementById('syncModalContent');
                const btn = document.getElementById('btnConfirmSync');
                
                modal.style.display = "flex";
                content.innerHTML = '<div class="flex justify-center p-8"><i class="fa-solid fa-circle-notch fa-spin text-4xl text-blue-500"></i></div>';
                btn.classList.add('hidden');

                fetch(`{{ route('admin.tmdb.seasons', ':tmdbId') }}`.replace(':tmdbId', serieTmdbId))
                    .then(r => r.json())
                    .then(data => {
                        if (data.error) {
                            content.innerHTML = `<div class="text-red-500 p-4">${data.error}</div>`;
                            return;
                        }

                        let html = `
                            <div class="mb-4 flex justify-between items-center">
                                <span class="text-sm text-neutral-400">Selecione as temporadas para importar/atualizar:</span>
                                <label class="flex items-center gap-2 cursor-pointer text-sm">
                                    <input type="checkbox" id="selectAllSync" checked onchange="toggleAllSync(this)"> Selecionar Todos
                                </label>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                        `;

                        data.seasons.forEach(season => {
                            html += `
                                <label class="flex items-center gap-3 p-3 bg-neutral-800 rounded hover:bg-neutral-700 cursor-pointer transition">
                                    <input type="checkbox" name="sync_seasons" value="${season.season_number}" checked class="sync-checkbox">
                                    <div>
                                        <div class="font-bold">Temporada ${season.season_number}</div>
                                        <div class="text-xs text-neutral-400">${season.episode_count} episódios</div>
                                    </div>
                                </label>
                            `;
                        });

                        html += '</div>';
                        content.innerHTML = html;
                        btn.classList.remove('hidden');
                    })
                    .catch(err => {
                        content.innerHTML = `<div class="text-red-500 p-4">Erro ao buscar dados: ${err.message}</div>`;
                    });
            }

            function closeSyncModal() {
                document.getElementById('syncModal').style.display = "none";
            }

            function toggleAllSync(el) {
                document.querySelectorAll('.sync-checkbox').forEach(cb => cb.checked = el.checked);
            }

            function confirmSyncSeasons() {
                const selected = Array.from(document.querySelectorAll('.sync-checkbox:checked')).map(cb => cb.value);
                
                if (selected.length === 0) {
                    alert('Selecione pelo menos uma temporada');
                    return;
                }

                const btn = document.getElementById('btnConfirmSync');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Sincronizando...';

                fetch(`{{ route('admin.tmdb.sync-seasons') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        tmdb_id: serieTmdbId,
                        series_id: serieId,
                        seasons: selected
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Erro ao sincronizar: ' + data.error);
                        btn.disabled = false;
                        btn.innerHTML = 'Sincronizar Selecionadas';
                    }
                })
                .catch(err => {
                    alert('Erro na requisição: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = 'Sincronizar Selecionadas';
                });
            }

            function openSeasonModal(id, status) {

                const modal = document.getElementById('seasonModal');

                modal.style.display = "flex";

                document.getElementById('seasonStatus').value = status;

                document.getElementById('seasonForm').action =
                    "{{ route('admin.series.seasons.update', ':id') }}".replace(':id', id);
            }

            function closeSeasonModal() {

                document.getElementById('seasonModal').style.display = "none";

            }
        </script>
    </section>

@endsection

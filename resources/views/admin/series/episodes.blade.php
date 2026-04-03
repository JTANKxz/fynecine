@extends('layouts.admin')

@section('title', 'Episódios')

@section('content')

    <section>

        <div class="flex justify-between items-center mb-4 gap-3">
            <h2 class="text-xl font-bold">
                Episódios - Temporada {{ $season->season_number }}
            </h2>

            <div class="flex gap-3">
                <button onclick="openSyncEpisodesModal()" 
                    class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700 text-sm flex items-center gap-2 transition">
                    <i class="fa-solid fa-sync"></i> Sincronizar TMDB
                </button>

                <a href="{{ route('admin.series.seasons', $season->series_id) }}"
                    class="bg-neutral-700 px-4 py-2 rounded hover:bg-neutral-600 transition text-sm">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>

        <div class="bg-neutral-900 rounded-lg overflow-hidden">

            <div class="table-container">

                <table class="w-full">

                    <thead class="bg-neutral-800">

                        <tr>

                            <th class="text-left p-4">ID</th>
                            <th class="text-left p-4">Episódio</th>
                            <th class="text-left p-4">Título</th>
                            <th class="text-left p-4">Duração</th>
                            <th class="text-left p-4">Status</th>
                            <th class="text-left p-4">Ações</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($episodes as $episode)
                            <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">

                                <td class="p-4">
                                    {{ $episode->id }}
                                </td>

                                <td class="p-4">
                                    Ep {{ $episode->episode_number }}
                                </td>

                                <td class="p-4 font-medium">
                                    {{ $episode->name }}
                                </td>

                                <td class="p-4">
                                    {{ $episode->duration ?? '-' }} min
                                </td>

                                <td class="p-4">

                                    @if ($episode->status == 'active')
                                        <span class="bg-green-600/20 text-green-400 px-2 py-1 rounded text-sm">
                                            Ativo
                                        </span>
                                    @else
                                        <span class="bg-red-600/20 text-red-400 px-2 py-1 rounded text-sm">
                                            Inativo
                                        </span>
                                    @endif

                                </td>

                                <td class="p-4 flex items-center gap-3">

                                    {{-- links do player --}}
                                    <a href="{{ route('admin.series.episodes.links', $episode->id) }}"
                                        class="text-green-500 hover:text-green-400" title="Players">

                                        <i class="fa-solid fa-play"></i>

                                    </a>

                                    {{-- editar --}}
                                    <button
                                        onclick="openEditModal({{ $episode->id }}, @js($episode->name), '{{ $episode->status }}')"
                                        class="text-blue-500 hover:text-blue-400" title="Editar">

                                        <i class="fa-solid fa-edit"></i>

                                    </button>

                                    {{-- deletar --}}
                                    <form action="{{ route('admin.series.episodes.delete', $episode->id) }}" method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button type="button" class="text-red-500 hover:text-red-400 swal-delete">

                                            <i class="fa-solid fa-trash"></i>

                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" class="text-center p-6 text-neutral-400">
                                    Nenhum episódio encontrado
                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="p-4">

                {{ $episodes->links() }}

            </div>

        </div>

        <x-swal />

        <div id="editModal" style="display:none" class="fixed inset-0 bg-black/60 flex items-center justify-center">

            <div class="bg-neutral-900 p-6 rounded-lg w-full max-w-md">

                <h2 class="text-lg font-bold mb-4">Editar Episódio</h2>

                <form id="editForm" method="POST">

                    @csrf
                    @method('PUT')

                    <div class="space-y-4">

                        <div>
                            <label class="text-sm text-neutral-400">Nome do Episódio</label>

                            <input type="text" name="name" id="episodeName"
                                class="w-full p-2 bg-neutral-800 rounded mt-1">
                        </div>

                        <div>
                            <label class="text-sm text-neutral-400">Status</label>

                            <select name="status" id="episodeStatus" class="w-full p-2 bg-neutral-800 rounded mt-1">

                                <option value="active">Ativo</option>
                                <option value="inactive">Inativo</option>

                            </select>

                        </div>

                    </div>

                    <div class="flex justify-end gap-3 mt-6">

                        <button type="button" onclick="closeModal()" class="bg-neutral-700 px-4 py-2 rounded">

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
                    <h2 class="text-xl font-bold">Sincronizar Episódios</h2>
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
                    <button id="btnConfirmSync" onclick="confirmSyncEpisodes()" class="bg-blue-600 px-6 py-2 rounded hover:bg-blue-700 hidden">
                        Sincronizar Selecionados
                    </button>
                </div>
            </div>
        </div>

        <script>
            let serieTmdbId = "{{ $season->series->tmdb_id }}";
            let serieId = "{{ $season->series_id }}";
            let seasonId = "{{ $season->id }}";
            let seasonNumber = "{{ $season->season_number }}";

            function openSyncEpisodesModal() {
                const modal = document.getElementById('syncModal');
                const content = document.getElementById('syncModalContent');
                const btn = document.getElementById('btnConfirmSync');
                
                modal.style.display = "flex";
                content.innerHTML = '<div class="flex justify-center p-8"><i class="fa-solid fa-circle-notch fa-spin text-4xl text-blue-500"></i></div>';
                btn.classList.add('hidden');

                fetch(`{{ route('admin.tmdb.episodes', [':tmdbId', ':seasonNumber']) }}`
                    .replace(':tmdbId', serieTmdbId)
                    .replace(':seasonNumber', seasonNumber))
                    .then(r => r.json())
                    .then(data => {
                        if (data.error) {
                            content.innerHTML = `<div class="text-red-500 p-4">${data.error}</div>`;
                            return;
                        }

                        let html = `
                            <div class="mb-4 flex justify-between items-center">
                                <span class="text-sm text-neutral-400">Selecione os episódios para importar/atualizar:</span>
                                <label class="flex items-center gap-2 cursor-pointer text-sm">
                                    <input type="checkbox" id="selectAllSync" checked onchange="toggleAllSync(this)"> Selecionar Todos
                                </label>
                            </div>
                            <div class="space-y-2">
                        `;

                        data.episodes.forEach(episode => {
                            html += `
                                <label class="flex items-center gap-3 p-3 bg-neutral-800 rounded hover:bg-neutral-700 cursor-pointer transition">
                                    <input type="checkbox" name="sync_episodes" value="${episode.episode_number}" checked class="sync-checkbox">
                                    <div class="flex-1">
                                        <div class="font-bold">Episódio ${episode.episode_number} - ${episode.name}</div>
                                        <div class="text-xs text-neutral-400 line-clamp-1">${episode.overview || 'Sem descrição'}</div>
                                    </div>
                                    <div class="text-xs text-neutral-500">${episode.runtime || 0} min</div>
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

            function confirmSyncEpisodes() {
                const selected = Array.from(document.querySelectorAll('.sync-checkbox:checked')).map(cb => cb.value);
                
                if (selected.length === 0) {
                    alert('Selecione pelo menos um episódio');
                    return;
                }

                const btn = document.getElementById('btnConfirmSync');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Sincronizando...';

                fetch(`{{ route('admin.tmdb.sync-episodes') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        tmdb_id: serieTmdbId,
                        series_id: serieId,
                        season_id: seasonId,
                        season_number: seasonNumber,
                        episodes: selected
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Erro ao sincronizar: ' + data.error);
                        btn.disabled = false;
                        btn.innerHTML = 'Sincronizar Selecionados';
                    }
                })
                .catch(err => {
                    alert('Erro na requisição: ' + err.message);
                    btn.disabled = false;
                    btn.innerHTML = 'Sincronizar Selecionados';
                });
            }

            function openEditModal(id, name, status) {

                const modal = document.getElementById('editModal');

                modal.style.display = "flex";

                document.getElementById('episodeName').value = name;
                document.getElementById('episodeStatus').value = status;

                document.getElementById('editForm').action = "{{ route('admin.series.episodes.update', ':id') }}".replace(':id', id);

            }

            function closeModal() {

                const modal = document.getElementById('editModal');

                modal.style.display = "none";

            }
        </script>

    </section>

@endsection

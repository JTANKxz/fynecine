@extends('layouts.admin')

@section('title', 'Episódios')

@section('content')

    <section>

        <div class="flex justify-between items-center mb-4">

            <h2 class="text-xl font-bold">
                Episódios - Temporada {{ $season->season_number }}
            </h2>

            <a href="{{ route('admin.series.seasons', $season->series_id) }}"
                class="bg-neutral-700 px-4 py-2 rounded hover:bg-neutral-600 transition text-sm">

                <i class="fa-solid fa-arrow-left mr-2"></i>Voltar

            </a>

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

        <script>
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

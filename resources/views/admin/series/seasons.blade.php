@extends('layouts.admin')

@section('title', 'Temporadas')

@section('content')

    <section>

        <div class="flex justify-between items-center mb-4">

            <h2 class="text-xl font-bold">
                Temporadas - {{ $serie->name }}
            </h2>

            <a href="{{ route('admin.series.index') }}" class="bg-neutral-700 px-4 py-2 rounded hover:bg-neutral-600 text-sm">

                Voltar

            </a>

        </div>

        <div class="bg-neutral-900 rounded-lg overflow-hidden">

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

        <script>
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

@extends('layouts.admin')

@section('title', 'Series')

@section('content')
    <section>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Catálogo de Séries</h2>
            <button class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
                <i class="fa-solid fa-plus mr-2"></i>Adicionar Série
            </button>
        </div>

        <!-- Form de Pesquisa -->
        <form method="GET" action="{{ route('admin.series.index') }}" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Pesquisar por título ou ano"
                class="px-4 py-2 rounded bg-neutral-800 text-white w-full">
            <button type="submit" class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition">
                Pesquisar
            </button>
        </form>

        <div class="bg-neutral-900 rounded-lg overflow-hidden">
            <div class="table-container">
                <table class="w-full">
                    <thead class="bg-neutral-800">
                        <tr>
                            <th class="text-left p-4">Capa</th>
                            <th class="text-left p-4">ID</th>
                            <th class="text-left p-4">Título</th>
                            <th class="text-left p-4">Ano</th>
                            <th class="text-left p-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($series as $serie)
                            <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                                <td class="p-2">
                                    <img src="{{ $serie->poster_path ? asset($serie->poster_path) : asset('images/placeholder.png') }}"
                                        class="movie-poster rounded">
                                </td>
                                <td class="p-4">{{ $serie->id }}</td>
                                <td class="p-4 font-medium">{{ $serie->name }}</td>
                                <td class="p-4">{{ $serie->first_air_year }}</td>
                                <td class="p-4">
                                    <button class="text-blue-500 hover:text-blue-400 mr-2">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>

                                    <a href="{{ route('admin.series.seasons', $serie->id) }}"
                                        class="text-green-500 hover:text-green-400 mr-2" title="Ver temporadas">

                                        <i class="fa-solid fa-layer-group"></i>

                                    </a>

                                    <form action="{{ route('admin.series.delete', $serie->id) }}" method="POST"
                                        class="inline-block">
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
                                <td colspan="5" class="text-center p-4">Nenhuma serie encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="p-4">
                {{ $series->links() }}
            </div>
        </div>

        <x-swal />
    </section>
@endsection

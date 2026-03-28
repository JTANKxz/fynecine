@extends('layouts.admin')

@section('title', 'Downloads - Séries')

@section('content')
<section>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold">Gerenciador de Downloads: Séries</h2>
            <p class="text-xs text-neutral-500">Selecione uma série para gerenciar os downloads por episódio.</p>
        </div>

        <div class="flex gap-3 items-center w-full md:w-auto">
            <a href="{{ route('admin.downloads.movies') }}" class="bg-neutral-800 border border-neutral-700 text-xs px-4 py-2 rounded font-bold hover:bg-neutral-700 transition whitespace-nowrap">
                <i class="fa-solid fa-film mr-1"></i> Filmes
            </a>
            <form action="{{ route('admin.downloads.series') }}" method="GET" class="flex gap-2 w-full md:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome ou TMDB ID..."
                       class="bg-neutral-900 border border-neutral-800 rounded px-3 py-1.5 text-sm outline-none focus:ring-1 focus:ring-netflix w-full md:w-64">
                <button type="submit" class="bg-neutral-800 px-4 py-1.5 rounded text-sm font-bold hover:bg-neutral-700">BUSCAR</button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-900 border border-green-600 text-green-100 px-4 py-2 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($series as $serie)
            <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-4 flex gap-4 items-center">
                <img src="{{ $serie->poster_path ? 'https://image.tmdb.org/t/p/w200'.$serie->poster_path : 'https://via.placeholder.com/200x300' }}"
                     class="w-12 h-16 object-cover rounded shadow-md flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <h3 class="font-bold text-white text-sm truncate">{{ $serie->name }}</h3>
                    <p class="text-[10px] text-neutral-500 uppercase font-mono">TMDB: {{ $serie->tmdb_id }}</p>
                    <p class="text-[10px] text-neutral-500">{{ $serie->first_air_year }}</p>
                </div>
                <a href="{{ route('admin.downloads.series.manage', $serie->id) }}"
                   class="bg-green-700 hover:bg-green-600 text-white text-xs font-bold px-3 py-2 rounded transition flex-shrink-0">
                    <i class="fa-solid fa-download"></i>
                </a>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $series->links() }}
    </div>
</section>
@endsection

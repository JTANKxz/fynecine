@extends('layouts.admin')

@section('title', 'Gerenciador de Links - Séries')

@section('content')
<section>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold">Gerenciador de Links: Séries</h2>
            <p class="text-xs text-neutral-500">Gestão massiva de players por temporada e episódio.</p>
        </div>

        <form action="{{ route('admin.links.series') }}" method="GET" class="flex gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome da Série ou TMDB..." 
                   class="bg-neutral-900 border border-neutral-800 rounded px-3 py-1.5 text-sm outline-none focus:ring-1 focus:ring-netflix w-full md:w-64">
            <button type="submit" class="bg-neutral-800 px-4 py-1.5 rounded text-sm font-bold hover:bg-neutral-700">BUSCAR</button>
        </form>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-900 border border-green-600 text-green-100 px-4 py-2 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($series as $serie)
            <div class="bg-neutral-900 border border-neutral-800 rounded-lg overflow-hidden group hover:border-netflix transition">
                <div class="relative aspect-video">
                    <img src="{{ $serie->backdrop_path ? 'https://image.tmdb.org/t/p/w500'.$serie->backdrop_path : 'https://via.placeholder.com/500x281' }}" 
                         class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition">
                    <div class="absolute inset-0 bg-gradient-to-t from-black to-transparent"></div>
                    <div class="absolute bottom-4 left-4">
                        <h3 class="font-bold text-white">{{ $serie->name }}</h3>
                        <p class="text-[10px] text-neutral-400 uppercase">{{ $serie->first_air_year }} • TMDB: {{ $serie->tmdb_id }}</p>
                    </div>
                </div>
                <div class="p-4 flex justify-between items-center">
                    <div class="text-xs text-neutral-500">
                        <span class="block">{{ $serie->seasons->count() }} Temporadas</span>
                    </div>
                    <a href="{{ route('admin.links.series.manage', $serie->id) }}" class="bg-netflix text-white px-4 py-1.5 rounded text-xs font-bold hover:bg-red-700 transition">
                        GERENCIAR LINKS
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $series->links() }}
    </div>
</section>

<script>
    function deleteEpLink(id) {
        if(confirm('Remover este link do episódio?')) {
            document.getElementById(`delete-eplink-${id}`).submit();
        }
    }
</script>
@endsection

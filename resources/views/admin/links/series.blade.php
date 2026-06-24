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

    <div class="space-y-6">
        @forelse ($series as $serie)
            <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-4">
                <h3 class="text-lg font-bold text-netflix mb-4">{{ $serie->name }}</h3>
                
                <div class="space-y-4">
                    @foreach ($serie->seasons as $season)
                        @if($season->episodes->count() > 0)
                        <div class="bg-neutral-800/50 border border-neutral-700 rounded p-3">
                            <h4 class="font-bold mb-2 text-neutral-300">Temporada {{ $season->season_number }}</h4>
                            
                            <div class="space-y-2">
                                @foreach ($season->episodes as $episode)
                                    @if($episode->links->count() > 0)
                                    <div class="bg-neutral-800 border border-neutral-700 p-2 rounded">
                                        <div class="text-sm font-semibold mb-2">Ep. {{ $episode->episode_number }} - {{ $episode->name }}</div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                            @foreach ($episode->links as $link)
                                            <div class="flex justify-between items-center bg-neutral-900 border border-neutral-800 p-2 rounded text-xs">
                                                <div class="truncate pr-2">
                                                    <span class="font-bold text-blue-400">[{{ strtoupper($link->type) }}]</span> 
                                                    {{ $link->name }} <span class="text-neutral-500">({{ $link->quality }})</span>
                                                </div>
                                                <div class="flex gap-3">
                                                    <a href="{{ route('admin.series.episodes.links.edit', $link->id) }}" class="text-blue-500 hover:text-blue-400" title="Editar"><i class="fa-solid fa-edit"></i></a>
                                                    <button onclick="deleteEpLink({{ $link->id }})" class="text-red-500 hover:text-red-400" title="Remover"><i class="fa-solid fa-trash"></i></button>
                                                    <form id="delete-eplink-{{ $link->id }}" action="{{ route('admin.series.episodes.links.delete', $link->id) }}" method="POST" class="hidden">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-8 text-center text-neutral-500">
                Nenhum link manual cadastrado para séries.
            </div>
        @endforelse
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

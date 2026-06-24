@extends('layouts.admin')

@section('title', 'Links de Episódios - Visão Global')

@section('content')
<section>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold">Links de Episódios <span class="text-netflix">— Visão Global</span></h2>
            <p class="text-xs text-neutral-500">Listagem direta de todos os links manuais cadastrados por série → temporada → episódio.</p>
        </div>

        <form action="{{ route('admin.links.series.global') }}" method="GET" class="flex gap-2 w-full md:w-auto">
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
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-netflix">{{ $serie->name }}</h3>
                    <a href="{{ route('admin.links.series.manage', $serie->id) }}" 
                       class="text-[10px] text-neutral-500 hover:text-white transition flex items-center gap-1">
                        <i class="fa-solid fa-gear"></i> Gerenciar completo
                    </a>
                </div>
                
                <div class="space-y-3">
                    @foreach ($serie->seasons as $season)
                        @if($season->episodes->count() > 0)
                        <div class="bg-neutral-800/40 border border-neutral-700/50 rounded p-3">
                            <h4 class="text-xs font-bold text-neutral-400 uppercase tracking-wider mb-2">
                                <i class="fa-solid fa-layer-group mr-1"></i> Temporada {{ $season->season_number }}
                            </h4>
                            
                            <div class="space-y-2">
                                @foreach ($season->episodes as $episode)
                                    @if($episode->links->count() > 0)
                                    <div class="bg-neutral-900/80 border border-neutral-800 p-2 rounded">
                                        <div class="text-xs font-semibold text-neutral-300 mb-1.5">
                                            Ep. {{ $episode->episode_number }} — {{ $episode->name }}
                                        </div>
                                        
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1.5">
                                            @foreach ($episode->links as $link)
                                            <div class="flex justify-between items-center bg-black/30 border border-neutral-800 px-2 py-1.5 rounded text-[11px]">
                                                <div class="truncate pr-2 flex items-center gap-1.5">
                                                    <span class="px-1 py-0.5 rounded text-[9px] font-bold 
                                                        {{ $link->type === 'mp4' || $link->type === 'm3u8' || $link->type === 'mkv' ? 'bg-green-900/50 text-green-400' : ($link->type === 'custom' ? 'bg-blue-900/50 text-blue-400' : ($link->type === 'private' ? 'bg-purple-900/50 text-purple-400' : 'bg-neutral-700 text-neutral-400')) }}">
                                                        {{ strtoupper($link->type) }}
                                                    </span>
                                                    <span class="truncate text-neutral-300">{{ $link->name }}</span>
                                                    <span class="text-neutral-600 shrink-0">{{ $link->quality }}</span>
                                                </div>
                                                <div class="flex gap-2 shrink-0">
                                                    <a href="{{ route('admin.series.episodes.links.edit', $link->id) }}" 
                                                       class="text-blue-500 hover:text-blue-400 transition" title="Editar">
                                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                                    </a>
                                                    <button onclick="deleteEpLink({{ $link->id }})" 
                                                            class="text-red-500 hover:text-red-400 transition" title="Remover">
                                                        <i class="fa-solid fa-trash text-[10px]"></i>
                                                    </button>
                                                    <form id="delete-eplink-{{ $link->id }}" 
                                                          action="{{ route('admin.series.episodes.links.delete', $link->id) }}" 
                                                          method="POST" class="hidden">
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
            <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-10 text-center text-neutral-500">
                <i class="fa-solid fa-link-slash text-3xl mb-3 block"></i>
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

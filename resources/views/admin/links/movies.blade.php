@extends('layouts.admin')

@section('title', 'Gerenciador de Links - Filmes')

@section('content')
<section>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold">Gerenciador de Links: Filmes</h2>
            <p class="text-xs text-neutral-500">Adicione ou edite players de filmes rapidamente.</p>
        </div>

        <form action="{{ route('admin.links.movies') }}" method="GET" class="flex gap-2 w-full md:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome ou TMDB ID..." 
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
        @foreach ($movies as $movie)
            <div class="bg-neutral-950 border border-neutral-900 rounded-lg overflow-hidden">
                <div class="bg-neutral-900/50 p-4 border-b border-neutral-900 flex flex-wrap justify-between items-center gap-4">
                    <div class="flex items-center gap-4">
                        <img src="{{ $movie->poster_path ? 'https://image.tmdb.org/t/p/w200'.$movie->poster_path : 'https://via.placeholder.com/200x300' }}" 
                             class="w-10 h-14 object-cover rounded shadow-lg">
                        <div>
                            <h3 class="font-bold text-white">{{ $movie->title }} ({{ $movie->release_year }})</h3>
                            <p class="text-[10px] text-neutral-500 font-mono uppercase">TMDB: {{ $movie->tmdb_id }}</p>
                        </div>
                    </div>

                    {{-- Form de Adição Rápida --}}
                    <form action="{{ route('admin.links.movies.store', $movie->id) }}" method="POST" class="flex flex-wrap items-center gap-2 bg-black/40 p-2 rounded border border-neutral-800">
                        @csrf
                        <input type="text" name="name" placeholder="Servidor" required class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs w-28 outline-none">
                        <input type="text" name="url" placeholder="URL do Player" required class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs w-40 outline-none">
                        <input type="text" name="quality" placeholder="Qualidade" class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs w-20 outline-none">
                        <input type="number" name="order" placeholder="Ord" class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs w-12 outline-none">
                        <select name="type" class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs outline-none">
                            <option value="embed">EMBED</option>
                            <option value="mp4">MP4</option>
                            <option value="m3u8">M3U8</option>
                        </select>
                        <select name="player_sub" class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs outline-none text-yellow-500 font-bold">
                            <option value="free">FREE</option>
                            <option value="premium">VIP</option>
                        </select>
                        <button type="submit" class="bg-netflix text-white px-3 py-1 rounded text-xs font-bold hover:bg-red-700 transition">
                            + ADD
                        </button>
                    </form>
                </div>

                <div class="p-4 bg-neutral-950/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse ($movie->playLinks as $link)
                            <div class="bg-neutral-900 border border-neutral-800 p-3 rounded flex flex-col gap-2 group transition hover:border-neutral-700 border-t-2 {{ $link->player_sub == 'premium' ? 'border-yellow-600' : 'border-blue-600' }}">
                                <form action="{{ route('admin.links.movies.update', $link->id) }}" method="POST" class="space-y-2">
                                    @csrf @method('PUT')
                                    <div class="flex justify-between items-center">
                                        <input type="text" name="name" value="{{ $link->name }}" class="bg-transparent border-none p-0 text-xs font-bold text-white w-24 outline-none">
                                        <div class="flex gap-2">
                                            <button type="submit" title="Salvar" class="text-blue-500 hover:text-white transition"><i class="fa-solid fa-floppy-disk text-[10px]"></i></button>
                                            <button type="button" onclick="deleteLink({{ $link->id }})" title="Excluir" class="text-red-500 hover:text-white transition"><i class="fa-solid fa-trash-can text-[10px]"></i></button>
                                        </div>
                                    </div>
                                    <input type="text" name="url" value="{{ $link->url }}" class="w-full bg-neutral-800 border-none rounded px-2 py-1 text-[10px] text-neutral-400 font-mono">
                                    
                                    <div class="grid grid-cols-2 gap-2 mt-2">
                                        <input type="text" name="quality" value="{{ $link->quality }}" placeholder="Qualidade" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px]">
                                        <input type="number" name="order" value="{{ $link->order }}" placeholder="Ordem" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px]">
                                    </div>


                                    <div class="flex justify-between items-center gap-2 pt-1 border-t border-neutral-800 mt-1">
                                        <select name="type" class="bg-transparent border-none text-[9px] p-0 outline-none">
                                            <option value="embed" {{ $link->type == 'embed' ? 'selected' : '' }}>EMBED</option>
                                            <option value="mp4" {{ $link->type == 'mp4' ? 'selected' : '' }}>MP4</option>
                                            <option value="m3u8" {{ $link->type == 'm3u8' ? 'selected' : '' }}>M3U8</option>
                                        </select>
                                        <select name="player_sub" class="bg-transparent border-none text-[9px] p-0 outline-none {{ $link->player_sub == 'premium' ? 'text-yellow-500' : 'text-blue-400' }}">
                                            <option value="free" {{ $link->player_sub == 'free' ? 'selected' : '' }}>FREE</option>
                                            <option value="premium" {{ $link->player_sub == 'premium' ? 'selected' : '' }}>VIP</option>
                                        </select>
                                    </div>
                                </form>
                                <form id="delete-link-{{ $link->id }}" action="{{ route('admin.links.movies.delete', $link->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        @empty
                            <p class="text-xs text-neutral-600 italic">Nenhum link configurado para este filme.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $movies->links() }}
    </div>
</section>

<script>
    function deleteLink(id) {
        if(confirm('Tem certeza que deseja apagar este player?')) {
            document.getElementById(`delete-link-${id}`).submit();
        }
    }
</script>
@endsection

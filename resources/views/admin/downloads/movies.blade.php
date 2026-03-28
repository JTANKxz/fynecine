@extends('layouts.admin')

@section('title', 'Downloads - Filmes')

@section('content')
<section>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold">Gerenciador de Downloads: Filmes</h2>
            <p class="text-xs text-neutral-500">Adicione links de download para cada filme.</p>
        </div>

        <div class="flex gap-3 items-center w-full md:w-auto">
            <a href="{{ route('admin.downloads.series') }}" class="bg-neutral-800 border border-neutral-700 text-xs px-4 py-2 rounded font-bold hover:bg-neutral-700 transition whitespace-nowrap">
                <i class="fa-solid fa-tv mr-1"></i> Séries
            </a>
            <form action="{{ route('admin.downloads.movies') }}" method="GET" class="flex gap-2 w-full md:w-auto">
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

    <div class="space-y-6">
        @foreach ($movies as $movie)
            <div class="bg-neutral-950 border border-neutral-900 rounded-lg overflow-hidden">
                {{-- Header do Filme --}}
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
                    <form action="{{ route('admin.downloads.movies.store', $movie->id) }}" method="POST"
                          class="flex flex-wrap items-center gap-2 bg-black/40 p-2 rounded border border-neutral-800">
                        @csrf
                        <input type="text" name="name" placeholder="Nome (ex: Download HD)" required
                               class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs w-36 outline-none">
                        <input type="text" name="url" placeholder="URL do arquivo" required
                               class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs w-44 outline-none">
                        <input type="text" name="quality" placeholder="Qualidade"
                               class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs w-20 outline-none">
                        <input type="text" name="size" placeholder="Tamanho"
                               class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs w-20 outline-none">
                        <input type="number" name="order" placeholder="Ord" value="0"
                               class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs w-12 outline-none">
                        <select name="type" class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs outline-none">
                            <option value="direct">DIRETO</option>
                            <option value="external">EXTERNO</option>
                        </select>
                        <select name="download_sub" class="bg-neutral-900 border border-neutral-800 rounded px-2 py-1 text-xs outline-none text-yellow-500 font-bold">
                            <option value="free">FREE</option>
                            <option value="premium">VIP</option>
                        </select>
                        <button type="submit" class="bg-green-700 text-white px-3 py-1 rounded text-xs font-bold hover:bg-green-600 transition">
                            <i class="fa-solid fa-download mr-1"></i>+ ADD
                        </button>
                    </form>
                </div>

                {{-- Links existentes --}}
                <div class="p-4 bg-neutral-950/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse ($movie->downloadLinks as $link)
                            <div class="bg-neutral-900 border border-neutral-800 p-3 rounded flex flex-col gap-2 border-t-2 {{ $link->download_sub == 'premium' ? 'border-yellow-600' : 'border-green-700' }}">
                                <form action="{{ route('admin.downloads.movies.update', $link->id) }}" method="POST" class="space-y-2">
                                    @csrf @method('PUT')
                                    <div class="flex justify-between items-center">
                                        <input type="text" name="name" value="{{ $link->name }}"
                                               class="bg-transparent border-none p-0 text-xs font-bold text-white w-28 outline-none">
                                        <div class="flex gap-2">
                                            <button type="submit" title="Salvar" class="text-blue-500 hover:text-white transition">
                                                <i class="fa-solid fa-floppy-disk text-[10px]"></i>
                                            </button>
                                            <button type="button" onclick="deleteDownloadLink({{ $link->id }})" title="Excluir"
                                                    class="text-red-500 hover:text-white transition">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <input type="text" name="url" value="{{ $link->url }}"
                                           class="w-full bg-neutral-800 border-none rounded px-2 py-1 text-[10px] text-neutral-400 font-mono">

                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="text" name="quality" value="{{ $link->quality }}" placeholder="Qualidade"
                                               class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px]">
                                        <input type="text" name="size" value="{{ $link->size }}" placeholder="Tamanho (ex: 2.1 GB)"
                                               class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px]">
                                    </div>

                                    <div class="flex justify-between items-center gap-2 pt-1 border-t border-neutral-800 mt-1">
                                        <select name="type" class="bg-transparent border-none text-[9px] p-0 outline-none">
                                            <option value="direct" {{ $link->type == 'direct' ? 'selected' : '' }}>DIRETO</option>
                                            <option value="external" {{ $link->type == 'external' ? 'selected' : '' }}>EXTERNO</option>
                                        </select>
                                        <select name="download_sub" class="bg-transparent border-none text-[9px] p-0 outline-none {{ $link->download_sub == 'premium' ? 'text-yellow-500' : 'text-green-400' }}">
                                            <option value="free" {{ $link->download_sub == 'free' ? 'selected' : '' }}>FREE</option>
                                            <option value="premium" {{ $link->download_sub == 'premium' ? 'selected' : '' }}>VIP</option>
                                        </select>
                                        <input type="number" name="order" value="{{ $link->order }}" placeholder="Ord"
                                               class="bg-neutral-800 border-none rounded px-2 py-1 text-[9px] w-12">
                                    </div>
                                </form>
                                <form id="delete-dl-{{ $link->id }}" action="{{ route('admin.downloads.movies.delete', $link->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            </div>
                        @empty
                            <p class="text-xs text-neutral-600 italic col-span-full">Nenhum link de download configurado para este filme.</p>
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
    function deleteDownloadLink(id) {
        if(confirm('Tem certeza que deseja apagar este link de download?')) {
            document.getElementById(`delete-dl-${id}`).submit();
        }
    }
</script>
@endsection

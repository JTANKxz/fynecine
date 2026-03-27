@extends('layouts.admin')

@section('title', 'Conteúdo da Network')

@section('content')
<section>
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-xl font-bold">{{ $network->name }} — Conteúdo</h2>
            <p class="text-xs text-neutral-500">Vincule filmes e séries a esta network.</p>
        </div>
        <a href="{{ route('admin.networks.index') }}" class="bg-neutral-700 px-4 py-2 rounded hover:bg-neutral-600 transition text-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i>Voltar
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-900 border border-green-600 text-green-100 px-4 py-2 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Busca e adição rápida --}}
    <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-4 mb-6">
        <h3 class="font-bold text-sm mb-3">Adicionar Conteúdo</h3>
        <div class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs text-neutral-500 mb-1">Tipo</label>
                <select id="searchType" class="bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-sm outline-none">
                    <option value="movie">Filme</option>
                    <option value="series">Série</option>
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs text-neutral-500 mb-1">Buscar</label>
                <input type="text" id="searchInput" placeholder="Digite o nome..."
                    class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-netflix">
            </div>
        </div>
        <div id="searchResults" class="mt-3 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-2"></div>
    </div>

    {{-- Filmes --}}
    @if($movies->count())
    <h3 class="font-bold text-sm mb-3 text-neutral-400 uppercase">Filmes ({{ $movies->count() }})</h3>
    <div class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-8 gap-3 mb-6">
        @foreach($movies as $movie)
            <div class="bg-neutral-900 border border-neutral-800 rounded overflow-hidden group relative">
                <img src="{{ $movie->poster_path ? 'https://image.tmdb.org/t/p/w200'.$movie->poster_path : 'https://via.placeholder.com/200x300' }}"
                     class="w-full aspect-[2/3] object-cover">
                <div class="p-1.5">
                    <p class="text-[10px] font-bold truncate">{{ $movie->title }}</p>
                </div>
                <form action="{{ route('admin.networks.content.remove', $network->id) }}" method="POST"
                    class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="content_id" value="{{ $movie->id }}">
                    <input type="hidden" name="content_type" value="movie">
                    <button type="submit" class="bg-red-600 text-white w-5 h-5 rounded-full text-[10px] flex items-center justify-center hover:bg-red-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
            </div>
        @endforeach
    </div>
    @endif

    {{-- Séries --}}
    @if($series->count())
    <h3 class="font-bold text-sm mb-3 text-neutral-400 uppercase">Séries ({{ $series->count() }})</h3>
    <div class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-8 gap-3 mb-6">
        @foreach($series as $serie)
            <div class="bg-neutral-900 border border-neutral-800 rounded overflow-hidden group relative">
                <img src="{{ $serie->poster_path ? 'https://image.tmdb.org/t/p/w200'.$serie->poster_path : 'https://via.placeholder.com/200x300' }}"
                     class="w-full aspect-[2/3] object-cover">
                <div class="p-1.5">
                    <p class="text-[10px] font-bold truncate">{{ $serie->name }}</p>
                </div>
                <form action="{{ route('admin.networks.content.remove', $network->id) }}" method="POST"
                    class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="content_id" value="{{ $serie->id }}">
                    <input type="hidden" name="content_type" value="series">
                    <button type="submit" class="bg-red-600 text-white w-5 h-5 rounded-full text-[10px] flex items-center justify-center hover:bg-red-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
            </div>
        @endforeach
    </div>
    @endif

    @if(!$movies->count() && !$series->count())
        <div class="bg-neutral-900 rounded-lg p-8 text-center text-neutral-500">
            <i class="fa-solid fa-film text-3xl mb-2"></i>
            <p class="text-sm">Nenhum conteúdo vinculado.</p>
        </div>
    @endif
</section>

<script>
let debounce;
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const searchType = document.getElementById('searchType');

searchInput.addEventListener('input', function() {
    clearTimeout(debounce);
    debounce = setTimeout(() => {
        const q = this.value;
        if (q.length < 2) { searchResults.innerHTML = ''; return; }

        fetch(`{{ route('admin.networks.search') }}?q=${encodeURIComponent(q)}&type=${searchType.value}`)
            .then(r => r.json())
            .then(data => {
                searchResults.innerHTML = data.map(item => `
                    <form action="{{ route('admin.networks.content.add', $network->id) }}" method="POST"
                        class="bg-neutral-800 border border-neutral-700 rounded p-2 cursor-pointer hover:border-netflix transition">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="content_id" value="${item.id}">
                        <input type="hidden" name="content_type" value="${searchType.value}">
                        <button type="submit" class="w-full text-left">
                            <p class="text-xs font-bold truncate">${item.name}</p>
                            <p class="text-[10px] text-green-400 mt-1">+ Adicionar</p>
                        </button>
                    </form>
                `).join('');
            });
    }, 300);
});
</script>
@endsection

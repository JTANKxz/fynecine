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

    {{-- Busca e adição rápida --}}
    <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold text-sm">Adicionar Conteúdo</h3>
            <div id="searchLoader" class="hidden text-netflix animate-spin text-sm">
                <i class="fa-solid fa-circle-notch"></i>
            </div>
        </div>
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
    <div id="movies-section" class="{{ $movies->count() ? '' : 'hidden' }} mb-6">
        <h3 class="font-bold text-sm mb-3 text-neutral-400 uppercase">Filmes (<span id="movies-count">{{ $movies->count() }}</span>)</h3>
        <div id="movies-grid" class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-8 gap-3">
            @foreach($movies as $movie)
                @include('admin.networks.partials.content_card', ['item' => $movie, 'type' => 'movie'])
            @endforeach
        </div>
    </div>

    {{-- Séries --}}
    <div id="series-section" class="{{ $series->count() ? '' : 'hidden' }} mb-6">
        <h3 class="font-bold text-sm mb-3 text-neutral-400 uppercase">Séries (<span id="series-count">{{ $series->count() }}</span>)</h3>
        <div id="series-grid" class="grid grid-cols-3 md:grid-cols-5 lg:grid-cols-8 gap-3">
            @foreach($series as $serie)
                @include('admin.networks.partials.content_card', ['item' => $serie, 'type' => 'series'])
            @endforeach
        </div>
    </div>

    <div id="empty-state" class="{{ !$movies->count() && !$series->count() ? '' : 'hidden' }} bg-neutral-900 rounded-lg p-8 text-center text-neutral-500">
        <i class="fa-solid fa-film text-3xl mb-2"></i>
        <p class="text-sm">Nenhum conteúdo vinculado.</p>
    </div>
</section>

<script>
const networkId = {{ $network->id }};
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const searchType = document.getElementById('searchType');
const searchLoader = document.getElementById('searchLoader');
let debounce;

// Toast configuration
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    background: '#171717',
    color: '#fff',
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

searchInput.addEventListener('input', function() {
    clearTimeout(debounce);
    const q = this.value;
    if (q.length < 2) { searchResults.innerHTML = ''; return; }

    searchLoader.classList.remove('hidden');
    debounce = setTimeout(() => {
        fetch(`{{ route('admin.networks.search') }}?q=${encodeURIComponent(q)}&type=${searchType.value}`)
            .then(r => r.json())
            .then(data => {
                searchLoader.classList.add('hidden');
                searchResults.innerHTML = data.map(item => `
                    <div onclick="addContent(${item.id}, '${searchType.value}')"
                        class="bg-neutral-800 border border-neutral-700 rounded p-2 cursor-pointer hover:border-netflix transition group relative">
                        <div class="h-24 w-full bg-neutral-900 rounded overflow-hidden mb-1">
                            <img src="${item.poster_path ? 'https://image.tmdb.org/t/p/w200' + item.poster_path : 'https://via.placeholder.com/200x300'}" class="w-full h-full object-cover">
                        </div>
                        <p class="text-[10px] font-bold truncate">${item.name}</p>
                        <p class="text-[9px] text-green-400 mt-0.5 opacity-0 group-hover:opacity-100 transition">+ Adicionar</p>
                    </div>
                `).join('');
            });
    }, 400);
});

function addContent(contentId, contentType) {
    const formData = new FormData();
    formData.append('content_id', contentId);
    formData.append('content_type', contentType);
    formData.append('_token', '{{ csrf_token() }}');

    fetch(`{{ route('admin.networks.content.add', $network->id) }}`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Toast.fire({ icon: 'success', title: data.message });
            appendContentToGrid(data.item);
            updateEmptyState();
        } else {
            Toast.fire({ icon: 'error', title: 'Erro ao adicionar conteúdo.' });
        }
    })
    .catch(err => {
        console.error(err);
        Toast.fire({ icon: 'error', title: 'Ocorreu um erro inesperado.' });
    });
}

function removeContent(contentId, contentType, button) {
    const card = button.closest('.content-card');
    const formData = new FormData();
    formData.append('content_id', contentId);
    formData.append('content_type', contentType);
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('_method', 'DELETE');

    fetch(`{{ route('admin.networks.content.remove', $network->id) }}`, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            Toast.fire({ icon: 'success', title: data.message });
            card.remove();
            updateCounts(contentType, -1);
            updateEmptyState();
        } else {
            Toast.fire({ icon: 'error', title: 'Erro ao remover conteúdo.' });
        }
    })
    .catch(err => {
        console.error(err);
        Toast.fire({ icon: 'error', title: 'Ocorreu um erro inesperado.' });
    });
}

function appendContentToGrid(item) {
    const gridId = item.content_type === 'movie' ? 'movies-grid' : 'series-grid';
    const sectionId = item.content_type === 'movie' ? 'movies-section' : 'series-section';
    const grid = document.getElementById(gridId);
    const section = document.getElementById(sectionId);

    // Check if already exists in grid to avoid duplicates in UI
    if (grid.querySelector(`[data-id="${item.id}"]`)) return;

    const html = `
        <div class="bg-neutral-900 border border-neutral-800 rounded overflow-hidden group relative content-card" data-id="${item.id}">
            <img src="${item.poster_path ? 'https://image.tmdb.org/t/p/w200' + item.poster_path : 'https://via.placeholder.com/200x300'}"
                 class="w-full aspect-[2/3] object-cover">
            <div class="p-1.5">
                <p class="text-[10px] font-bold truncate">${item.name}</p>
            </div>
            <button onclick="removeContent(${item.id}, '${item.content_type}', this)"
                class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition bg-red-600 text-white w-5 h-5 rounded-full text-[10px] flex items-center justify-center hover:bg-red-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    `;

    grid.insertAdjacentHTML('afterbegin', html);
    section.classList.remove('hidden');
    updateCounts(item.content_type, 1);
}

function updateCounts(type, delta) {
    const countSpan = document.getElementById(type === 'movie' ? 'movies-count' : 'series-count');
    const section = document.getElementById(type === 'movie' ? 'movies-section' : 'series-section');
    let current = parseInt(countSpan.innerText);
    current += delta;
    countSpan.innerText = current;

    if (current <= 0) {
        section.classList.add('hidden');
    } else {
        section.classList.remove('hidden');
    }
}

function updateEmptyState() {
    const moviesCount = parseInt(document.getElementById('movies-count').innerText);
    const seriesCount = parseInt(document.getElementById('series-count').innerText);
    const emptyState = document.getElementById('empty-state');

    if (moviesCount === 0 && seriesCount === 0) {
        emptyState.classList.remove('hidden');
    } else {
        emptyState.classList.add('hidden');
    }
}
</script>
@endsection

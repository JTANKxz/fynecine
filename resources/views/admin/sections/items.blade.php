@extends('layouts.admin')

@section('title', 'Itens da Seção')

@section('content')
<section>
    <div class="flex justify-between items-center mb-4">
        <div>
            <h2 class="text-xl font-bold">Itens: {{ $section->title }}</h2>
            <p class="text-xs text-neutral-500">Adicione filmes ou séries a esta seção custom.</p>
        </div>
        <a href="{{ route('admin.sections.index') }}" class="bg-neutral-700 px-4 py-2 rounded hover:bg-neutral-600 transition text-sm">
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

    {{-- Itens atuais --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
        @forelse($items as $item)
            @php $content = $item->content; @endphp
            @if($content)
                <div class="bg-neutral-900 border border-neutral-800 rounded overflow-hidden group relative">
                    <img src="{{ $content->poster_path ? 'https://image.tmdb.org/t/p/w200'.$content->poster_path : 'https://via.placeholder.com/200x300' }}"
                         class="w-full aspect-[2/3] object-cover">
                    <div class="p-2">
                        <p class="text-xs font-bold truncate">{{ $item->content_type === 'movie' ? $content->title : $content->name }}</p>
                        <span class="text-[10px] text-neutral-500 uppercase">{{ $item->content_type }}</span>
                    </div>
                    <form action="{{ route('admin.sections.items.remove', $item->id) }}" method="POST"
                        class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white w-6 h-6 rounded-full text-xs flex items-center justify-center hover:bg-red-700">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </form>
                </div>
            @endif
        @empty
            <div class="col-span-full text-center text-neutral-500 py-8">
                <i class="fa-solid fa-film text-3xl mb-2"></i>
                <p class="text-sm">Nenhum item adicionado.</p>
            </div>
        @endforelse
    </div>
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

        fetch(`{{ route('admin.sections.search') }}?q=${encodeURIComponent(q)}&type=${searchType.value}`)
            .then(r => r.json())
            .then(data => {
                searchResults.innerHTML = data.map(item => `
                    <form action="{{ route('admin.sections.items.add', $section->id) }}" method="POST"
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

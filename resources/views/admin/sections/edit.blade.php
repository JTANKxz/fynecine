@extends('layouts.admin')

@section('title', 'Editar Seção')

@section('content')
<section>
    <h2 class="text-xl font-bold mb-4">Editar Seção: {{ $section->title }}</h2>

    @if($errors->any())
        <div class="mb-4 bg-red-900 border border-red-600 text-red-100 px-4 py-2 rounded text-sm">
            <ul>@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.sections.update', $section->id) }}" method="POST" class="bg-neutral-900 p-5 rounded space-y-4">
        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Título da Seção</label>
                <input type="text" name="title" value="{{ old('title', $section->title) }}"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none" required>
            </div>

            <div>
                <label class="block text-sm text-neutral-400 mb-1">Tipo de Seção</label>
                <select name="type" id="sectionType"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    onchange="toggleFields()">
                    <option value="custom" {{ old('type', $section->type) == 'custom' ? 'selected' : '' }}>Custom</option>
                    <option value="genre" {{ old('type', $section->type) == 'genre' ? 'selected' : '' }}>Por Gênero</option>
                    <option value="trending" {{ old('type', $section->type) == 'trending' ? 'selected' : '' }}>Trending</option>
                    <option value="network" {{ old('type', $section->type) == 'network' ? 'selected' : '' }}>Itens de uma Network</option>
                    <option value="networks" {{ old('type', $section->type) == 'networks' ? 'selected' : '' }}>Lista de Networks (Cards Quadrados)</option>
                    <option value="recently_added" {{ old('type', $section->type) == 'recently_added' ? 'selected' : '' }}>Recém Adicionados</option>
                </select>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Tipo de Conteúdo</label>
                <select name="content_type" class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                    <option value="both" {{ old('content_type', $section->content_type) == 'both' ? 'selected' : '' }}>Filmes e Séries</option>
                    <option value="movie" {{ old('content_type', $section->content_type) == 'movie' ? 'selected' : '' }}>Somente Filmes</option>
                    <option value="series" {{ old('content_type', $section->content_type) == 'series' ? 'selected' : '' }}>Somente Séries</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Limite de Itens</label>
                <input type="number" name="limit" value="{{ old('limit', $section->limit) }}"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none" min="1" max="50">
            </div>
            <div class="flex items-end pb-1">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                        class="w-5 h-5 rounded bg-neutral-800 border-neutral-600 text-netflix focus:ring-netflix"
                        {{ old('is_active', $section->is_active) ? 'checked' : '' }}>
                    <span class="text-sm text-neutral-300">Ativa</span>
                </label>
            </div>
        </div>

        <div id="genreField" class="hidden">
            <label class="block text-sm text-neutral-400 mb-1">Gênero</label>
            <select name="genre_id" class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <option value="">Selecione</option>
                @foreach($genres as $g)
                    <option value="{{ $g->id }}" {{ old('genre_id', $section->genre_id) == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                @endforeach
            </select>
        </div>

        <div id="networkField" class="hidden">
            <label class="block text-sm text-neutral-400 mb-1">Network</label>
            <select name="network_id" class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <option value="">Selecione</option>
                @foreach($networks as $n)
                    <option value="{{ $n->id }}" {{ old('network_id', $section->network_id) == $n->id ? 'selected' : '' }}>{{ $n->name }}</option>
                @endforeach
            </select>
        </div>

        <div id="trendingField" class="hidden">
            <label class="block text-sm text-neutral-400 mb-1">Período</label>
            <select name="trending_period" class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <option value="today" {{ old('trending_period', $section->trending_period) == 'today' ? 'selected' : '' }}>Hoje</option>
                <option value="week" {{ old('trending_period', $section->trending_period) == 'week' ? 'selected' : '' }}>Esta Semana</option>
                <option value="all_time" {{ old('trending_period', $section->trending_period) == 'all_time' ? 'selected' : '' }}>Todo Tempo</option>
            </select>
        </div>

        <div class="flex gap-3">
            <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">Atualizar</button>
            <a href="{{ route('admin.sections.index') }}" class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">Cancelar</a>
        </div>
    </form>
</section>

<script>
function toggleFields() {
    const type = document.getElementById('sectionType').value;
    document.getElementById('genreField').classList.toggle('hidden', type !== 'genre');
    document.getElementById('networkField').classList.toggle('hidden', type !== 'network');
    document.getElementById('trendingField').classList.toggle('hidden', type !== 'trending');
}
toggleFields();
</script>
@endsection

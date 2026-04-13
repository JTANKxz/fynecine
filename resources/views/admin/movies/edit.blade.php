@extends('layouts.admin')

@section('title', 'Editar Filme: ' . $movie->title)

@section('content')
<section>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold border-l-4 border-netflix pl-3">Editar Detalhes: <span class="text-neutral-400 font-medium">{{ $movie->title }}</span></h2>
        <a href="{{ route('admin.movies.index') }}" class="bg-neutral-800 hover:bg-neutral-700 text-white px-4 py-2 rounded transition text-sm">
            <i class="fa-solid fa-arrow-left mr-2"></i>Voltar
        </a>
    </div>

    <div class="bg-neutral-900 rounded-xl border border-neutral-800 p-6 shadow-2xl">
        <form action="{{ route('admin.movies.update', $movie->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Título -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400">Título do Filme</label>
                    <input type="text" name="title" value="{{ old('title', $movie->title) }}" required
                           class="w-full px-4 py-2 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:border-netflix focus:outline-none transition">
                </div>

                <!-- Slug -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400">Slug (URL)</label>
                    <input type="text" name="slug" value="{{ old('slug', $movie->slug) }}" required
                           class="w-full px-4 py-2 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:border-netflix focus:outline-none transition text-neutral-500">
                </div>

                <!-- Ano e Duração -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-neutral-400">Ano de Lançamento</label>
                        <input type="text" name="release_year" value="{{ old('release_year', $movie->release_year) }}"
                               class="w-full px-4 py-2 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:border-netflix focus:outline-none transition">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-neutral-400">Duração (Minutos)</label>
                        <input type="number" name="runtime" value="{{ old('runtime', $movie->runtime) }}"
                               class="w-full px-4 py-2 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:border-netflix focus:outline-none transition">
                    </div>
                </div>

                <!-- Nota e Classificação -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-neutral-400">Nota (IA/TMDB)</label>
                        <input type="text" name="rating" value="{{ old('rating', $movie->rating) }}"
                               class="w-full px-4 py-2 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:border-netflix focus:outline-none transition">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-neutral-400">Classificação (Age Rating)</label>
                        <input type="text" name="age_rating" value="{{ old('age_rating', $movie->age_rating) }}"
                               class="w-full px-4 py-2 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:border-netflix focus:outline-none transition">
                    </div>
                </div>

                <!-- Categoria -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400">Categoria de Conteúdo</label>
                    <select name="content_category_id" class="w-full px-4 py-2 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:border-netflix focus:outline-none transition">
                        <option value="">Nenhuma</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $movie->content_category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Trailer Key -->
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400">YouTube Trailer Key</label>
                    <div class="flex gap-2">
                        <span class="flex items-center px-3 bg-neutral-700 rounded-l-lg text-xs text-neutral-400">watch?v=</span>
                        <input type="text" name="trailer_key" value="{{ old('trailer_key', $movie->trailer_key) }}"
                               class="w-full px-4 py-2 bg-neutral-800 text-white rounded-r-lg border border-neutral-700 focus:border-netflix focus:outline-none transition">
                    </div>
                </div>

                <!-- Poster Path -->
                <div class="md:col-span-2 space-y-2">
                    <label class="block text-sm font-bold text-neutral-400">Caminho do Pôster (URL)</label>
                    <input type="text" name="poster_path" value="{{ old('poster_path', $movie->poster_path) }}"
                           class="w-full px-4 py-2 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:border-netflix focus:outline-none transition">
                </div>

                <!-- Backdrop Path -->
                <div class="md:col-span-2 space-y-2">
                    <label class="block text-sm font-bold text-neutral-400">Caminho do Fundo (Backdrop URL)</label>
                    <input type="text" name="backdrop_path" value="{{ old('backdrop_path', $movie->backdrop_path) }}"
                           class="w-full px-4 py-2 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:border-netflix focus:outline-none transition">
                </div>

                <!-- Gêneros -->
                <div class="md:col-span-2 space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 mb-2">Gêneros</label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 p-4 bg-neutral-800/50 rounded-xl border border-neutral-700">
                        @foreach($genres as $genre)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-neutral-700 p-2 rounded transition group">
                                <input type="checkbox" name="genres[]" value="{{ $genre->id }}" 
                                       class="w-4 h-4 rounded accent-netflix"
                                       {{ in_array($genre->id, $movie->genres->pluck('id')->toArray()) ? 'checked' : '' }}>
                                <span class="text-xs text-neutral-300 group-hover:text-white">{{ $genre->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Sinopse -->
                <div class="md:col-span-2 space-y-2">
                    <label class="block text-sm font-bold text-neutral-400">Sinopse (Overview)</label>
                    <textarea name="overview" rows="6"
                              class="w-full px-4 py-2 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:border-netflix focus:outline-none transition resize-none">{{ old('overview', $movie->overview) }}</textarea>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-neutral-800 flex justify-end gap-3">
                <a href="{{ route('admin.movies.index') }}" class="bg-neutral-800 hover:bg-neutral-700 text-white px-6 py-2 rounded-lg transition font-medium">
                    Cancelar
                </a>
                <button type="submit" class="bg-netflix hover:bg-purple-700 text-white px-8 py-2 rounded-lg transition font-bold shadow-lg shadow-purple-900/20">
                    <i class="fa-solid fa-save mr-2"></i>Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</section>
@endsection

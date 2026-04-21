@extends('layouts.admin')

@section('title', isset($section) ? 'Editar Seção Home' : 'Nova Seção Home')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.adult.home-sections.index') }}" class="text-neutral-400 hover:text-white transition">
            <i class="fa-solid fa-arrow-left text-xl"></i>
        </a>
        <h2 class="text-2xl font-bold">{{ isset($section) ? 'Editar Seção Home' : 'Nova Seção Home' }}</h2>
    </div>

    <form action="{{ isset($section) ? route('admin.adult.home-sections.update', $section->id) : route('admin.adult.home-sections.store') }}" method="POST" class="bg-neutral-900 p-6 rounded-lg border border-neutral-800 space-y-6 shadow-2xl">
        {{ csrf_field() }}
        @if(isset($section))
            @method('PUT')
        @endif

        <div>
            <label class="block text-sm font-medium text-neutral-400 mb-2">Título da Seção (Ex: Destaques, Recentes, etc)</label>
            <input type="text" name="title" value="{{ old('title', $section->title ?? '') }}" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Tipo de Dados</label>
                <select name="type" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition" required>
                    <option value="trending" {{ (isset($section) && $section->type == 'trending') ? 'selected' : '' }}>Bombando (Trending)</option>
                    <option value="recent" {{ (isset($section) && $section->type == 'recent') ? 'selected' : '' }}>Adicionados Recentemente</option>
                    <option value="models_carousel" {{ (isset($section) && $section->type == 'models_carousel') ? 'selected' : '' }}>Modelos (Scroll Lateral)</option>
                    <option value="video_grid" {{ (isset($section) && $section->type == 'video_grid') ? 'selected' : '' }}>Grid de Vídeos</option>
                    <option value="photo_grid" {{ (isset($section) && $section->type == 'photo_grid') ? 'selected' : '' }}>Grid de Fotos</option>
                    <option value="collections" {{ (isset($section) && $section->type == 'collections') ? 'selected' : '' }}>Coleções (OnlyFans, Privacy, etc)</option>
                    <option value="categories_grid" {{ (isset($section) && $section->type == 'categories_grid') ? 'selected' : '' }}>Grid de Categorias</option>
                    <option value="custom" {{ (isset($section) && $section->type == 'custom') ? 'selected' : '' }}>Customizado (Manual)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Limite de Itens</label>
                <input type="number" name="limit" value="{{ old('limit', $section->limit ?? 15) }}" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Ordem</label>
                <input type="number" name="order" value="{{ old('order', $section->order ?? 0) }}" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition">
            </div>
            <div class="flex items-center pt-8">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" class="sr-only peer" {{ old('is_active', $section->is_active ?? true) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-netflix"></div>
                    <span class="ms-3 text-sm font-medium text-neutral-400">Ativo na Home</span>
                </label>
            </div>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full bg-netflix py-3 rounded font-bold hover:bg-neutral-200 hover:text-black transition duration-300">
                {{ isset($section) ? 'Atualizar Seção' : 'Criar Seção' }}
            </button>
        </div>
    </form>
</div>
@endsection

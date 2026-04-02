@extends('layouts.admin')

@section('title', isset($category) ? 'Editar Categoria' : 'Nova Categoria')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('admin.categories.index') }}" class="text-neutral-500 hover:text-white flex items-center gap-2 mb-4 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Voltar para Lista
        </a>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">{{ isset($category) ? 'Editar Categoria' : 'Nova Categoria' }}</h1>
        <p class="text-neutral-400 mt-1">Configure o nome, ícone e visibilidade da categoria.</p>
    </div>

    <form action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}" 
          method="POST" 
          class="bg-neutral-900 border border-neutral-800 rounded-2xl p-8 space-y-6">
        @csrf
        @if(isset($category)) @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <label class="text-sm font-medium text-neutral-400">Nome da Categoria</label>
                <input type="text" name="name" 
                       value="{{ old('name', $category->name ?? '') }}" 
                       placeholder="Ex: Animes" required
                       class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:border-netflix focus:ring-1 focus:ring-netflix outline-none transition-all placeholder:text-neutral-700">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-neutral-400">Ícone (FontAwesome)</label>
                <input type="text" name="icon" 
                       value="{{ old('icon', $category->icon ?? 'fa-solid fa-folder') }}" 
                       placeholder="Ex: fa-solid fa-tv"
                       class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:border-netflix focus:ring-1 focus:ring-netflix outline-none transition-all placeholder:text-neutral-700 font-mono text-sm">
            </div>

            <div class="space-y-2">
                <label class="text-sm font-medium text-neutral-400">Ordem de Exibição</label>
                <input type="number" name="order" 
                       value="{{ old('order', $category->order ?? 0) }}" 
                       required
                       class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:border-netflix focus:ring-1 focus:ring-netflix outline-none transition-all placeholder:text-neutral-700">
            </div>
        </div>

        <div class="flex flex-col gap-4 bg-neutral-950/50 p-6 rounded-2xl border border-neutral-800">
            <div class="flex items-center justify-between group">
                <div>
                    <label class="font-bold text-white block">Categoria Ativa</label>
                    <span class="text-xs text-neutral-500">Se desativado, o conteúdo não será carregado na API.</span>
                </div>
                <input type="checkbox" name="is_active" 
                       {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}
                       class="w-6 h-6 rounded-full border-2 border-neutral-700 bg-neutral-900 checked:bg-emerald-500 transition-all checked:border-emerald-400 accent-emerald-500">
            </div>

            <div class="flex items-center justify-between group pt-4 border-t border-neutral-800/50">
                <div>
                    <label class="font-bold text-white block">Exibir na Navegação</label>
                    <span class="text-xs text-neutral-500">Se ativado, aparecerá no menu inferior do aplicativo.</span>
                </div>
                <input type="checkbox" name="is_nav_visible" 
                       {{ old('is_nav_visible', $category->is_nav_visible ?? true) ? 'checked' : '' }}
                       class="w-6 h-6 rounded-full border-2 border-neutral-700 bg-neutral-900 checked:bg-blue-500 transition-all checked:border-blue-400 accent-blue-500">
            </div>
        </div>

        <div class="flex gap-4 pt-4">
            <button type="submit" 
                    class="flex-1 px-8 py-4 bg-netflix hover:bg-netflix/90 text-white font-bold rounded-xl shadow-lg shadow-netflix/20 transition-all active:scale-95">
                {{ isset($category) ? 'Atualizar Categoria' : 'Criar Categoria' }}
            </button>
        </div>
    </form>
</div>
@endsection

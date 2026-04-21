@extends('layouts.admin')

@section('title', isset($category) ? 'Editar Categoria' : 'Nova Categoria')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.adult.categories.index') }}" class="text-neutral-400 hover:text-white transition">
            <i class="fa-solid fa-arrow-left text-xl"></i>
        </a>
        <h2 class="text-2xl font-bold">{{ isset($category) ? 'Editar Categoria' : 'Nova Categoria' }}</h2>
    </div>

    <form action="{{ isset($category) ? route('admin.adult.categories.update', $category->id) : route('admin.adult.categories.store') }}" method="POST" class="bg-neutral-900 p-6 rounded-lg border border-neutral-800 space-y-6">
        @csrf
        @if(isset($category))
            @method('PUT')
        @endif

        <div>
            <label class="block text-sm font-medium text-neutral-400 mb-2">Nome da Categoria</label>
            <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-neutral-400 mb-2">Ícone (FontAwesome Class)</label>
            <input type="text" name="icon" value="{{ old('icon', $category->icon ?? 'fa-solid fa-folder') }}" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition" placeholder="ex: fa-solid fa-heart">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Ordem de Exibição</label>
                <input type="number" name="order" value="{{ old('order', $category->order ?? 0) }}" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition">
            </div>
            <div class="flex items-center pt-8">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" class="sr-only peer" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-netflix"></div>
                    <span class="ms-3 text-sm font-medium text-neutral-400">Ativo</span>
                </label>
            </div>
        </div>

        <button type="submit" class="w-full bg-netflix py-3 rounded font-bold hover:bg-netflix/80 transition">
            {{ isset($category) ? 'Atualizar Categoria' : 'Criar Categoria' }}
        </button>
    </form>
</div>
@endsection

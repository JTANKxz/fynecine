@extends('layouts.admin')

@section('title', 'Nova Coleção Adulta')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.adult.collections.index') }}" class="text-neutral-400 hover:text-white flex items-center gap-2 mb-2">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
    <h2 class="text-2xl font-bold">Nova Coleção Adulta</h2>
</div>

<form action="{{ route('admin.adult.collections.store') }}" method="POST" class="max-w-2xl bg-neutral-900 p-6 rounded-lg border border-neutral-800">
    @csrf
    
    <div class="mb-4">
        <label for="title" class="block text-sm font-medium text-neutral-400 mb-1">Título</label>
        <input type="text" name="title" id="title" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix" required>
    </div>

    <div class="mb-4">
        <label for="cover_url" class="block text-sm font-medium text-neutral-400 mb-1">URL da Capa</label>
        <input type="url" name="cover_url" id="cover_url" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
    </div>

    <div class="mb-4">
        <label for="description" class="block text-sm font-medium text-neutral-400 mb-1">Descrição</label>
        <textarea name="description" id="description" rows="3" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix"></textarea>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div>
            <label for="order" class="block text-sm font-medium text-neutral-400 mb-1">Ordem</label>
            <input type="number" name="order" id="order" value="0" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
        </div>
        <div class="flex items-end pb-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" class="sr-only peer" checked>
                <div class="w-11 h-6 bg-neutral-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-netflix relative"></div>
                <span class="text-sm font-medium text-neutral-400">Ativo</span>
            </label>
        </div>
    </div>

    <button type="submit" class="w-full bg-netflix py-3 rounded font-bold hover:bg-netflix/80 transition">
        Criar Coleção
    </button>
</form>
@endsection

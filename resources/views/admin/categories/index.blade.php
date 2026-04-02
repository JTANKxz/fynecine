@extends('layouts.admin')

@section('title', 'Gerenciar Categorias')

@section('content')
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-3xl font-extrabold text-white tracking-tight">Categorias de Conteúdo</h1>
        <p class="text-neutral-400 mt-1">Gerencie as divisões principais do seu aplicativo (Ex: Animes, Filmes).</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" 
       class="bg-netflix hover:bg-netflix/90 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-netflix/20 transition-all active:scale-95 flex items-center gap-2">
        <i class="fa-solid fa-plus"></i>
        Nova Categoria
    </a>
</div>

<div class="bg-neutral-900 border border-neutral-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-neutral-800 bg-neutral-950/50">
                    <th class="px-6 py-4 text-xs font-bold text-neutral-500 uppercase tracking-wider">Ordem</th>
                    <th class="px-6 py-4 text-xs font-bold text-neutral-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-4 text-xs font-bold text-neutral-500 uppercase tracking-wider">Slug</th>
                    <th class="px-6 py-4 text-xs font-bold text-neutral-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-neutral-500 uppercase tracking-wider">Navegação</th>
                    <th class="px-6 py-4 text-xs font-bold text-neutral-500 uppercase tracking-wider text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse($categories as $category)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-4 font-mono text-neutral-500">{{ $category->order }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-neutral-800 flex items-center justify-center text-netflix">
                                <i class="{{ $category->icon ?? 'fa-solid fa-folder' }} text-lg"></i>
                            </div>
                            <span class="font-bold text-white">{{ $category->name }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-neutral-400 font-mono text-sm">{{ $category->slug }}</td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $category->is_active ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                            {{ $category->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase {{ $category->is_nav_visible ? 'bg-blue-500/10 text-blue-500' : 'bg-neutral-800 text-neutral-500' }}">
                            {{ $category->is_nav_visible ? 'Visível' : 'Oculto' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('admin.categories.edit', $category) }}" 
                               class="p-2 bg-neutral-800 hover:bg-neutral-700 text-white rounded-lg transition-colors">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Tem certeza?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="p-2 bg-neutral-800 hover:bg-rose-500/20 hover:text-rose-500 text-white rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-20 text-center text-neutral-500">
                        Nenhuma categoria encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

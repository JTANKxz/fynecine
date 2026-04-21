@extends('layouts.admin')

@section('title', 'Categorias Adultas')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Categorias Adultas</h2>
    <a href="{{ route('admin.adult.categories.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-netflix/80 flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Nova Categoria
    </a>
</div>

<div class="bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-neutral-950 border-b border-neutral-800 text-neutral-400 text-sm">
                <th class="p-4">Ordem</th>
                <th class="p-4">Nome</th>
                <th class="p-4">Slug</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-right">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-800">
            @forelse($categories as $category)
            <tr class="hover:bg-neutral-800/50 transition">
                <td class="p-4 w-20">{{ $category->order }}</td>
                <td class="p-4 flex items-center gap-3">
                    @if($category->icon)
                        <i class="{{ $category->icon }} text-netflix"></i>
                    @endif
                    <span class="font-bold">{{ $category->name }}</span>
                </td>
                <td class="p-4 text-sm text-neutral-400">{{ $category->slug }}</td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded-full text-[10px] uppercase font-bold {{ $category->is_active ? 'bg-green-600/20 text-green-400 border border-green-600/50' : 'bg-red-600/20 text-red-400 border border-red-600/50' }}">
                        {{ $category->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td class="p-4 text-right space-x-2">
                    <a href="{{ route('admin.adult.categories.edit', $category->id) }}" class="text-blue-400 hover:text-blue-300">
                        <i class="fa-solid fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.adult.categories.destroy', $category->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-400" onclick="return confirm('Tem certeza?')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="p-10 text-center text-neutral-500 italic">Nenhuma categoria encontrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

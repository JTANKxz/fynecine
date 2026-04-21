@extends('layouts.admin')

@section('title', 'Coleções Adultas')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Coleções Adultas</h2>
    <a href="{{ route('admin.adult.collections.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-netflix/80 flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Nova Coleção
    </a>
</div>

<div class="bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-neutral-950 border-b border-neutral-800 text-neutral-400 text-sm">
                <th class="p-4">Ordem</th>
                <th class="p-4">Capa</th>
                <th class="p-4">Título</th>
                <th class="p-4">Itens</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-right">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-800">
            @forelse($collections as $collection)
            <tr class="hover:bg-neutral-800/50 transition">
                <td class="p-4 w-20">{{ $collection->order }}</td>
                <td class="p-4">
                    @if($collection->cover_url)
                        <img src="{{ $collection->cover_url }}" class="w-16 h-10 object-cover rounded border border-neutral-700" alt="Capa">
                    @else
                        <div class="w-16 h-10 bg-neutral-800 rounded flex items-center justify-center">
                            <i class="fa-solid fa-image text-neutral-600 text-xs"></i>
                        </div>
                    @endif
                </td>
                <td class="p-4">
                    <span class="font-bold">{{ $collection->title }}</span>
                </td>
                <td class="p-4">
                    <span class="text-sm text-neutral-400">{{ $collection->galleries_count ?? $collection->galleries()->count() }} galerias</span>
                </td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded-full text-[10px] uppercase font-bold {{ $collection->is_active ? 'bg-green-600/20 text-green-400 border border-green-600/50' : 'bg-red-600/20 text-red-400 border border-red-600/50' }}">
                        {{ $collection->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td class="p-4 text-right space-x-2">
                    <a href="{{ route('admin.adult.collections.edit', $collection->id) }}" class="text-blue-400 hover:text-blue-300">
                        <i class="fa-solid fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.adult.collections.destroy', $collection->id) }}" method="POST" class="inline">
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
                <td colspan="6" class="p-10 text-center text-neutral-500 italic">Nenhuma coleção encontrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

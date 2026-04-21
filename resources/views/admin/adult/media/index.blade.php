@extends('layouts.admin')

@section('title', 'Mídias Avulsas Adultas')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Mídias Avulsas</h2>
    <a href="{{ route('admin.adult.media.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-netflix/80 flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Nova Mídia
    </a>
</div>

<div class="bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-neutral-950 border-b border-neutral-800 text-neutral-400 text-sm">
                <th class="p-4 w-32">Preview</th>
                <th class="p-4">Título</th>
                <th class="p-4">Tipo</th>
                <th class="p-4">Contexto</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-right">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-800">
            @forelse($media as $item)
            <tr class="hover:bg-neutral-800/50 transition">
                <td class="p-4">
                    @if($item->type === 'image')
                        <img src="{{ $item->url }}" class="w-24 h-16 object-cover rounded border border-neutral-700" alt="Preview">
                    @else
                        <div class="w-24 h-16 bg-neutral-800 rounded flex items-center justify-center font-bold text-neutral-600 text-[10px]">
                            VÍDEO
                        </div>
                    @endif
                </td>
                <td class="p-4">
                    <span class="font-bold">{{ $item->title ?? 'Sem título' }}</span>
                    <div class="text-[10px] text-neutral-500 truncate w-48">{{ $item->url }}</div>
                </td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded text-[10px] uppercase font-bold bg-neutral-800 text-neutral-300">
                        {{ $item->type === 'image' ? 'Foto' : 'Vídeo' }}
                    </span>
                </td>
                <td class="p-4 text-xs text-neutral-400">
                    @if($item->model)
                        <div class="mb-1">Modelo: <span class="text-white">{{ $item->model->name }}</span></div>
                    @endif
                    @if($item->category)
                        <div>Categoria: <span class="text-white">{{ $item->category->name }}</span></div>
                    @endif
                    @if(!$item->model && !$item->category)
                        <span class="italic">Avulsa</span>
                    @endif
                </td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded-full text-[10px] uppercase font-bold {{ $item->is_active ? 'bg-green-600/20 text-green-400 border border-green-600/50' : 'bg-red-600/20 text-red-400 border border-red-600/50' }}">
                        {{ $item->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td class="p-4 text-right space-x-2">
                    <a href="{{ route('admin.adult.media.edit', $item->id) }}" class="text-blue-400 hover:text-blue-300">
                        <i class="fa-solid fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.adult.media.destroy', $item->id) }}" method="POST" class="inline">
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
                <td colspan="6" class="p-10 text-center text-neutral-500 italic">Nenhuma mídia avulsa encontrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $media->links() }}
</div>
@endsection

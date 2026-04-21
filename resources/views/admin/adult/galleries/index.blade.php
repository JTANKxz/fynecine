@extends('layouts.admin')

@section('title', 'Galerias Adultas')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div class="flex items-baseline gap-3">
        <h2 class="text-2xl font-bold">Galerias</h2>
        <span class="text-xs text-neutral-500 uppercase tracking-widest">Fotos & Vídeos</span>
    </div>
    <a href="{{ route('admin.adult.galleries.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-netflix/80 flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Nova Galeria
    </a>
</div>

<div class="bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-neutral-950 border-b border-neutral-800 text-neutral-400 text-xs uppercase">
                <th class="p-4">Capa</th>
                <th class="p-4">Título / Modelo</th>
                <th class="p-4">Categoria</th>
                <th class="p-4">Tipo</th>
                <th class="p-4">Mídias</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-right">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-800">
            @forelse($galleries as $gallery)
            <tr class="hover:bg-neutral-800/50 transition group">
                <td class="p-4 w-24">
                    <img src="{{ $gallery->cover_url ?? 'https://via.placeholder.com/150x150?text=No+Cover' }}" class="w-16 h-16 object-cover rounded shadow-lg shadow-black/50">
                </td>
                <td class="p-4">
                    <div class="font-bold text-white">{{ $gallery->title }}</div>
                    <div class="text-xs text-netflix font-medium">@if($gallery->model) {{ $gallery->model->name }} @else Sem Modelo @endif</div>
                </td>
                <td class="p-4">
                    <span class="text-xs text-neutral-400">@if($gallery->category) {{ $gallery->category->name }} @else - @endif</span>
                </td>
                <td class="p-4">
                    <div class="flex gap-2">
                        @if($gallery->type == 'photo' || $gallery->type == 'both')
                            <i class="fa-solid fa-camera text-blue-400 text-xs" title="Fotos"></i>
                        @endif
                        @if($gallery->type == 'video' || $gallery->type == 'both')
                            <i class="fa-solid fa-video text-red-400 text-xs" title="Vídeos"></i>
                        @endif
                    </div>
                </td>
                <td class="p-4">
                    <a href="{{ route('admin.adult.galleries.media', $gallery->id) }}" class="inline-flex items-center gap-1.5 bg-neutral-800 px-2 py-1 rounded text-xs text-neutral-300 hover:bg-neutral-700 hover:text-white transition">
                        <i class="fa-solid fa-images"></i> {{ $gallery->media->count() }} itens
                    </a>
                </td>
                <td class="p-4">
                    <span class="px-2 py-1 rounded-full text-[10px] uppercase font-bold {{ $gallery->is_active ? 'bg-green-600/20 text-green-400 border border-green-600/50' : 'bg-red-600/20 text-red-400 border border-red-600/50' }}">
                        {{ $gallery->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td class="p-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.adult.galleries.edit', $gallery->id) }}" class="p-2 text-neutral-400 hover:text-blue-400 transition" title="Editar Galeria">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.adult.galleries.destroy', $gallery->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-neutral-500 hover:text-red-500 transition" onclick="return confirm('Excluir galeria e todas as mídias?')">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="p-20 text-center text-neutral-500 italic">Nenhuma galeria encontrada.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

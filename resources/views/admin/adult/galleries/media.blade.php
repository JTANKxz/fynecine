@extends('layouts.admin')

@section('title', 'Gerenciar Mídia - ' . $gallery->title)

@section('content')
<div class="flex items-center gap-4 mb-6">
    <a href="{{ route('admin.adult.galleries.index') }}" class="text-neutral-400 hover:text-white transition">
        <i class="fa-solid fa-arrow-left text-xl"></i>
    </a>
    <div>
        <h2 class="text-2xl font-bold">Gerenciar Mídia</h2>
        <p class="text-neutral-500 text-sm italic">{{ $gallery->title }} ({{ $gallery->model->name ?? 'Sem Modelo' }})</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Formulário de Adição Rápida --}}
    <div class="lg:col-span-1">
        <form action="{{ route('admin.adult.galleries.media.add', $gallery->id) }}" method="POST" class="bg-neutral-900 border border-neutral-800 rounded-lg p-6 sticky top-24">
            @csrf
            <h3 class="text-lg font-bold mb-6 flex items-center gap-2">
                <i class="fa-solid fa-plus-circle text-netflix"></i> Adicionar Item
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Tipo de Mídia</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="image" class="peer hidden" checked>
                            <div class="bg-black border border-neutral-800 p-3 rounded-lg text-center peer-checked:border-netflix peer-checked:bg-netflix/10 transition">
                                <i class="fa-solid fa-image mb-1"></i>
                                <div class="text-[10px] uppercase font-bold">Imagem</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="video" class="peer hidden">
                            <div class="bg-black border border-neutral-800 p-3 rounded-lg text-center peer-checked:border-netflix peer-checked:bg-netflix/10 transition">
                                <i class="fa-solid fa-video mb-1"></i>
                                <div class="text-[10px] uppercase font-bold">Vídeo</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">URL da Mídia</label>
                    <textarea name="url" rows="3" class="w-full bg-black border border-neutral-800 rounded p-3 text-xs focus:outline-none focus:border-netflix transition" placeholder="https://..." required></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Título (Opcional)</label>
                    <input type="text" name="title" class="w-full bg-black border border-neutral-800 rounded p-3 text-sm focus:outline-none focus:border-netflix transition">
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Ordem</label>
                    <input type="number" name="order" value="{{ $gallery->media->count() + 1 }}" class="w-full bg-black border border-neutral-800 rounded p-3 text-sm focus:outline-none focus:border-netflix transition">
                </div>

                <button type="submit" class="w-full bg-netflix py-3 rounded font-bold hover:scale-[1.02] active:scale-[0.98] transition mt-4">
                     Confirmar e Adicionar
                </button>
            </div>
        </form>
    </div>

    {{-- Grid de Itens Existentes --}}
    <div class="lg:col-span-2">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @forelse($media as $item)
            <div class="bg-neutral-900 border border-neutral-800 rounded-lg overflow-hidden group">
                <div class="aspect-square relative bg-black">
                    @if($item->type == 'image')
                        <img src="{{ $item->url }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-red-500">
                            <i class="fa-solid fa-film text-4xl mb-2"></i>
                            <span class="text-[10px] font-bold uppercase tracking-tighter">VÍDEO CLIP</span>
                        </div>
                    @endif
                    
                    {{-- Overlay de Ações --}}
                    <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-4">
                        <form action="{{ route('admin.adult.galleries.media.remove', $item->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-10 h-10 bg-red-600 rounded-full flex items-center justify-center hover:scale-110 transition" onclick="return confirm('Remover este item?')">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="p-2 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-neutral-500">ORDEM: {{ $item->order }}</span>
                    @if($item->type == 'video')
                        <i class="fa-solid fa-play-circle text-netflix text-xs" title="Video"></i>
                    @else
                        <i class="fa-solid fa-image text-blue-400 text-[10px]" title="Imagem"></i>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full p-20 text-center bg-neutral-900 rounded-lg border border-neutral-800 border-dashed border-neutral-700">
                <i class="fa-solid fa-photo-film text-4xl text-neutral-700 mb-4"></i>
                <p class="text-neutral-500 italic">Nenhum mídia adicionada a esta galeria.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Nova Mídia Adulta')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.adult.media.index') }}" class="text-neutral-400 hover:text-white flex items-center gap-2 mb-2">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
    <h2 class="text-2xl font-bold">Nova Mídia</h2>
</div>

<form action="{{ route('admin.adult.media.store') }}" method="POST" class="max-w-2xl bg-neutral-900 p-6 rounded-lg border border-neutral-800">
    @csrf
    
    <div class="mb-4 text-sm text-yellow-500 bg-yellow-500/10 p-3 rounded border border-yellow-500/20 italic">
        <i class="fa-solid fa-circle-info mr-1"></i> Se não selecionar uma Galeria, a mídia será salva como "Avulsa".
    </div>

    <div class="mb-4">
        <label for="adult_gallery_id" class="block text-sm font-medium text-neutral-400 mb-1">Galeria (Opcional)</label>
        <select name="adult_gallery_id" id="adult_gallery_id" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
            <option value="">-- Mídia Avulsa --</option>
            @foreach($galleries as $gallery)
                <option value="{{ $gallery->id }}">{{ $gallery->title }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label for="adult_model_id" class="block text-sm font-medium text-neutral-400 mb-1">Modelo (Opcional)</label>
            <select name="adult_model_id" id="adult_model_id" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
                <option value="">Nenhum</option>
                @foreach($models as $model)
                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="adult_category_id" class="block text-sm font-medium text-neutral-400 mb-1">Categoria (Opcional)</label>
            <select name="adult_category_id" id="adult_category_id" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
                <option value="">Nenhuma</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mb-4">
        <label for="title" class="block text-sm font-medium text-neutral-400 mb-1">Título/Nome</label>
        <input type="text" name="title" id="title" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
    </div>

    <div class="mb-4">
        <label for="url" class="block text-sm font-medium text-neutral-400 mb-1">URL (Mídia/Embed)</label>
        <input type="text" name="url" id="url" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix" required>
    </div>

    <div class="mb-4">
        <label for="thumbnail" class="block text-sm font-medium text-neutral-400 mb-1">URL da Thumbnail (Opcional)</label>
        <input type="text" name="thumbnail" id="thumbnail" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
        <p class="text-xs text-neutral-500 mt-1">Se vazio, o app tentará capturar do vídeo ou usará a foto da modelo.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label for="type" class="block text-sm font-medium text-neutral-400 mb-1">Tipo</label>
            <select name="type" id="type" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
                <option value="image">Foto/Imagem</option>
                <option value="video">Vídeo</option>
            </select>
        </div>
        <div id="player_type_container" class="hidden">
            <label for="player_type" class="block text-sm font-medium text-neutral-400 mb-1">Tipo de Player</label>
            <select name="player_type" id="player_type" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
                <option value="direct">Direto (MP4/HLS)</option>
                <option value="embed">Embed (Iframe/WebView)</option>
                <option value="sniffer">Sniffer (Detectar em Site)</option>
            </select>
        </div>
    </div>

    <div id="proportion_container" class="mb-4 hidden">
        <label for="proportion" class="block text-sm font-medium text-neutral-400 mb-1">Proporção (Aspect Ratio)</label>
        <select name="proportion" id="proportion" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
            <option value="16:9">Horizontal (16:9)</option>
            <option value="9:16">Vertical / Reels (9:16)</option>
            <option value="4:3">Quadrado/Antigo (4:3)</option>
            <option value="1:1">Quadrado (1:1)</option>
        </select>
        <p class="text-xs text-neutral-500 mt-1">Isso define como o modal será aberto no App.</p>
    </div>

    <script>
        document.getElementById('type').addEventListener('change', function() {
            const playerContainer = document.getElementById('player_type_container');
            const proportionContainer = document.getElementById('proportion_container');
            if (this.value === 'video') {
                playerContainer.classList.remove('hidden');
                proportionContainer.classList.remove('hidden');
            } else {
                playerContainer.classList.add('hidden');
                proportionContainer.classList.add('hidden');
            }
        });
    </script>

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
        Salvar Mídia
    </button>
</form>
@endsection

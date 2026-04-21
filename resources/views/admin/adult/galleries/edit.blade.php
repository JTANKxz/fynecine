@extends('layouts.admin')

@section('title', isset($gallery) ? 'Editar Galeria' : 'Nova Galeria')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.adult.galleries.index') }}" class="text-neutral-400 hover:text-white transition">
            <i class="fa-solid fa-arrow-left text-xl"></i>
        </a>
        <h2 class="text-2xl font-bold">{{ isset($gallery) ? 'Editar Galeria' : 'Nova Galeria' }}</h2>
    </div>

    <form action="{{ isset($gallery) ? route('admin.adult.galleries.update', $gallery->id) : route('admin.adult.galleries.store') }}" method="POST" class="bg-neutral-900 border border-neutral-800 rounded-lg overflow-hidden shadow-2xl">
        @csrf
        @if(isset($gallery))
            @method('PUT')
        @endif

        <div class="p-6 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Título --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Título da Galeria</label>
                    <input type="text" name="title" value="{{ old('title', $gallery->title ?? '') }}" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition" required placeholder="Ex: Ensaio de Verão 2024">
                </div>

                {{-- Modelo --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Modelo / Performer</label>
                    <select name="adult_model_id" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition">
                        <option value="">Selecione a Modelo (Opcional)</option>
                        @foreach($models as $model)
                            <option value="{{ $model->id }}" {{ (isset($gallery) && $gallery->adult_model_id == $model->id) || old('adult_model_id') == $model->id ? 'selected' : '' }}>
                                {{ $model->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Categoria --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Categoria</label>
                    <select name="adult_category_id" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition">
                        <option value="">Selecione a Categoria</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (isset($gallery) && $gallery->adult_category_id == $category->id) || old('adult_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo de Conteúdo --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Tipo de Conteúdo</label>
                    <select name="type" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition" required>
                        <option value="photo" {{ (isset($gallery) && $gallery->type == 'photo') || old('type') == 'photo' ? 'selected' : '' }}>Somente Fotos</option>
                        <option value="video" {{ (isset($gallery) && $gallery->type == 'video') || old('type') == 'video' ? 'selected' : '' }}>Somente Vídeos</option>
                        <option value="both" {{ (isset($gallery) && $gallery->type == 'both') || old('type') == 'both' ? 'selected' : '' }}>Misto (Fotos e Vídeos)</option>
                    </select>
                </div>

                {{-- Ordem --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Ordem</label>
                    <input type="number" name="order" value="{{ old('order', $gallery->order ?? 0) }}" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition">
                </div>

                {{-- Coleção (Ex: OnlyFans, Privacy) --}}
                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Coleção (Opcional)</label>
                    <select name="collection" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition">
                        <option value="">Nenhuma Coleção</option>
                        <option value="OnlyFans" {{ (isset($gallery) && $gallery->collection == 'OnlyFans') || old('collection') == 'OnlyFans' ? 'selected' : '' }}>OnlyFans</option>
                        <option value="Privacy" {{ (isset($gallery) && $gallery->collection == 'Privacy') || old('collection') == 'Privacy' ? 'selected' : '' }}>Privacy</option>
                        <option value="Platinum" {{ (isset($gallery) && $gallery->collection == 'Platinum') || old('collection') == 'Platinum' ? 'selected' : '' }}>Platinum</option>
                        <option value="Premium" {{ (isset($gallery) && $gallery->collection == 'Premium') || old('collection') == 'Premium' ? 'selected' : '' }}>Premium</option>
                    </select>
                </div>
            </div>

            {{-- Capa URL --}}
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">URL da Capa</label>
                <div class="flex gap-4">
                    <div class="w-24 h-24 bg-black border border-neutral-800 rounded flex-shrink-0 overflow-hidden" id="cover-preview">
                         @if(isset($gallery) && $gallery->cover_url)
                            <img src="{{ $gallery->cover_url }}" class="w-full h-full object-cover">
                         @else
                            <div class="w-full h-full flex items-center justify-center text-neutral-800"><i class="fa-solid fa-image text-3xl"></i></div>
                         @endif
                    </div>
                    <input type="url" name="cover_url" id="cover_url_input" value="{{ old('cover_url', $gallery->cover_url ?? '') }}" 
                        oninput="updateCoverPreview(this.value)"
                        class="flex-1 bg-black border border-neutral-800 rounded p-3 h-12 self-end focus:outline-none focus:border-netflix transition" placeholder="https://...">
                </div>
            </div>

            {{-- Descrição --}}
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Descrição (Opcional)</label>
                <textarea name="description" rows="3" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition">{{ old('description', $gallery->description ?? '') }}</textarea>
            </div>

            <div class="flex items-center">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" class="sr-only peer" {{ old('is_active', $gallery->is_active ?? true) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-netflix"></div>
                    <span class="ms-3 text-sm font-medium text-neutral-400">Ativa e Visível</span>
                </label>
            </div>
        </div>

        <div class="bg-neutral-950 p-6 flex justify-end border-t border-neutral-800">
            <button type="submit" class="bg-netflix px-8 py-3 rounded font-bold hover:scale-105 active:scale-95 transition">
                {{ isset($gallery) ? 'Salvar Alterações' : 'Criar Galeria' }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function updateCoverPreview(url) {
        const preview = document.getElementById('cover-preview');
        if (url && (url.startsWith('http') || url.startsWith('/'))) {
            preview.innerHTML = `<img src="${url}" class="w-full h-full object-cover">`;
        } else {
            preview.innerHTML = `<div class="w-full h-full flex items-center justify-center text-neutral-800"><i class="fa-solid fa-image text-3xl"></i></div>`;
        }
    }
</script>
@endpush
@endsection

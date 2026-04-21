@extends('layouts.admin')

@section('title', isset($model) ? 'Editar Modelo' : 'Nova Modelo')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('admin.adult.models.index') }}" class="text-neutral-400 hover:text-white transition">
            <i class="fa-solid fa-arrow-left text-xl"></i>
        </a>
        <h2 class="text-2xl font-bold">{{ isset($model) ? 'Editar Modelo' : 'Nova Modelo' }}</h2>
    </div>

    <form action="{{ isset($model) ? route('admin.adult.models.update', $model->id) : route('admin.adult.models.store') }}" method="POST" class="space-y-6">
        @csrf
        @if(isset($model))
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Coluna Foto --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-neutral-900 p-4 rounded-lg border border-neutral-800">
                    <label class="block text-sm font-medium text-neutral-400 mb-4">Foto de Perfil (URL)</label>
                    <div id="photo-preview" class="aspect-[3/4] rounded-lg bg-black border border-neutral-800 overflow-hidden mb-4 flex items-center justify-center">
                        @if(isset($model) && $model->photo_url)
                            <img src="{{ $model->photo_url }}" class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-user text-5xl text-neutral-800"></i>
                        @endif
                    </div>
                    <input type="url" name="photo_url" id="photo_url_input" value="{{ old('photo_url', $model->photo_url ?? '') }}" 
                        oninput="updatePreview('photo-preview', this.value)"
                        class="w-full bg-black border border-neutral-800 rounded p-2 text-xs focus:outline-none focus:border-netflix transition" placeholder="https://...">
                </div>

                <div class="bg-neutral-900 p-4 rounded-lg border border-neutral-800">
                    <label class="block text-sm font-medium text-neutral-400 mb-4">Capa / Background (URL)</label>
                    <input type="url" name="cover_url" value="{{ old('cover_url', $model->cover_url ?? '') }}" 
                        class="w-full bg-black border border-neutral-800 rounded p-2 text-xs focus:outline-none focus:border-netflix transition" placeholder="https://...">
                </div>
            </div>

            {{-- Coluna Dados --}}
            <div class="lg:col-span-2 space-y-6 bg-neutral-900 p-6 rounded-lg border border-neutral-800">
                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Nome Artístico</label>
                    <input type="text" name="name" value="{{ old('name', $model->name ?? '') }}" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-neutral-400 mb-2">Biografia / Descrição</label>
                    <textarea name="biography" rows="4" class="w-full bg-black border border-neutral-800 rounded p-3 focus:outline-none focus:border-netflix transition">{{ old('biography', $model->biography ?? '') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-400 mb-2">Instagram (Username)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-neutral-500">@</span>
                            <input type="text" name="instagram" value="{{ old('instagram', $model->instagram ?? '') }}" class="w-full bg-black border border-neutral-800 rounded p-3 pl-8 focus:outline-none focus:border-netflix transition">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-400 mb-2">Twitter (Username)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-3 text-neutral-500">@</span>
                            <input type="text" name="twitter" value="{{ old('twitter', $model->twitter ?? '') }}" class="w-full bg-black border border-neutral-800 rounded p-3 pl-8 focus:outline-none focus:border-netflix transition">
                        </div>
                    </div>
                </div>

                <div class="flex items-center pt-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" class="sr-only peer" {{ old('is_active', $model->is_active ?? true) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-netflix"></div>
                        <span class="ms-3 text-sm font-medium text-neutral-400">Ativa no Catálogo</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-netflix py-4 rounded font-bold hover:bg-white hover:text-black transition duration-300">
                    {{ isset($model) ? 'Salvar Alterações' : 'Cadastrar Modelo' }}
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function updatePreview(id, url) {
        const preview = document.getElementById(id);
        if (url && (url.startsWith('http') || url.startsWith('/'))) {
            preview.innerHTML = `<img src="${url}" class="w-full h-full object-cover">`;
        } else {
            preview.innerHTML = `<i class="fa-solid fa-user text-5xl text-neutral-800"></i>`;
        }
    }
</script>
@endpush
@endsection

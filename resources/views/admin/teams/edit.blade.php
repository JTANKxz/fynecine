@extends('layouts.admin')

@section('title', 'Editar Time')

@section('content')
<div class="max-w-xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Editar Time: {{ $team->name }}</h2>

    <form method="POST" action="{{ route('admin.teams.update', $team) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-bold mb-2">Nome do Time *</label>
            <input type="text" name="name" value="{{ old('name', $team->name) }}" required
                   class="w-full bg-neutral-900 border border-neutral-700 rounded p-3 text-white focus:border-netflix focus:outline-none">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-bold mb-2">Imagem (URL)</label>
            <input type="text" name="image_url" value="{{ old('image_url', $team->image_url) }}" id="image_url"
                   class="w-full bg-neutral-900 border border-neutral-700 rounded p-3 text-white focus:border-netflix focus:outline-none"
                   placeholder="https://exemplo.com/logo.png"
                   oninput="previewImage()">
        </div>

        <div class="text-center text-neutral-500 text-xs font-bold">— OU —</div>

        <div>
            <label class="block text-sm font-bold mb-2">Upload de Imagem</label>
            <input type="file" name="image_upload" accept="image/*"
                   class="w-full bg-neutral-900 border border-neutral-700 rounded p-3 text-white file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:bg-netflix file:text-white file:text-sm file:cursor-pointer"
                   onchange="previewUpload(event)">
        </div>

        {{-- Preview --}}
        <div id="preview-container" class="{{ $team->image_url ? '' : 'hidden' }}">
            <label class="block text-sm font-bold mb-2">Preview</label>
            <div class="w-24 h-24 bg-neutral-800 rounded-lg flex items-center justify-center overflow-hidden border border-neutral-700">
                <img id="preview-img" src="{{ $team->image_url }}" alt="Preview" class="w-full h-full object-contain">
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-netflix hover:bg-purple-700 px-6 py-3 rounded font-bold transition flex-1">
                <i class="fa-solid fa-check mr-1"></i> Salvar
            </button>
            <a href="{{ route('admin.teams.index') }}" class="bg-neutral-800 hover:bg-neutral-700 px-6 py-3 rounded font-bold transition text-center">
                Cancelar
            </a>
        </div>
    </form>
</div>

<script>
function previewImage() {
    const url = document.getElementById('image_url').value;
    const container = document.getElementById('preview-container');
    const img = document.getElementById('preview-img');
    if (url) {
        img.src = url;
        container.classList.remove('hidden');
    } else {
        container.classList.add('hidden');
    }
}

function previewUpload(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('preview-container').classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection

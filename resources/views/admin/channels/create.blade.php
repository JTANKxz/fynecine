@extends('layouts.admin')

@section('title', 'Criar Canal')

@section('content')
<section>
    <h2 class="text-xl font-bold mb-4">Novo Canal de TV</h2>

    @if($errors->any())
        <div class="mb-4 bg-red-900 border border-red-600 text-red-100 px-4 py-2 rounded text-sm">
            <ul>@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.channels.store') }}" method="POST" class="bg-neutral-900 p-5 rounded space-y-4">
        @csrf

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Nome do Canal</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="Ex: Globo, SBT, Record" required>
            </div>

            <div>
                <label class="block text-sm text-neutral-400 mb-1">URL da Imagem / Logo</label>
                <input type="text" name="image_url" value="{{ old('image_url') }}"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="https://exemplo.com/logo.png">
            </div>
        </div>

        <div>
            <label class="block text-sm text-neutral-400 mb-1">Categorias (opcional)</label>
            <select name="categories[]" multiple
                class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none min-h-[120px]">
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ in_array($cat->id, old('categories', [])) ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <p class="text-xs text-neutral-500 mt-1">Segure Ctrl para selecionar múltiplas</p>
        </div>

        <div class="flex gap-3">
            <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">
                Salvar Canal
            </button>
            <a href="{{ route('admin.channels.index') }}"
                class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">
                Cancelar
            </a>
        </div>
    </form>
</section>
@endsection

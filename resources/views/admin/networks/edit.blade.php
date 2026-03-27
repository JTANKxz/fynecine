@extends('layouts.admin')

@section('title', 'Editar Network')

@section('content')
<section>
    <h2 class="text-xl font-bold mb-4">Editar Network: {{ $network->name }}</h2>

    @if($errors->any())
        <div class="mb-4 bg-red-900 border border-red-600 text-red-100 px-4 py-2 rounded text-sm">
            <ul>@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.networks.update', $network->id) }}" method="POST" class="bg-neutral-900 p-5 rounded space-y-4">
        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Nome da Network</label>
                <input type="text" name="name" value="{{ old('name', $network->name) }}"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none" required>
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">URL da Imagem / Logo (opcional)</label>
                <input type="text" name="image_url" value="{{ old('image_url', $network->image_url) }}"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
            </div>
        </div>

        @if($network->image_url)
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Preview</label>
                <img src="{{ $network->image_url }}" class="h-16 object-contain rounded bg-neutral-800 p-2">
            </div>
        @endif

        <div class="flex gap-3">
            <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">Atualizar</button>
            <a href="{{ route('admin.networks.index') }}" class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">Cancelar</a>
        </div>
    </form>
</section>
@endsection

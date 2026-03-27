@extends('layouts.admin')

@section('title', 'Criar Categoria de Canal')

@section('content')
<section>
    <h2 class="text-xl font-bold mb-4">Nova Categoria de Canal</h2>

    @if($errors->any())
        <div class="mb-4 bg-red-900 border border-red-600 text-red-100 px-4 py-2 rounded text-sm">
            <ul>@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif

    <form action="{{ route('admin.channel-categories.store') }}" method="POST" class="bg-neutral-900 p-5 rounded space-y-4">
        @csrf

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Nome da Categoria</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="Ex: Esportes, Notícias, Filmes" required>
            </div>

            <div>
                <label class="block text-sm text-neutral-400 mb-1">Vincular Canais (opcional)</label>
                <select name="channels[]" multiple
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none min-h-[120px]">
                    @foreach($channels as $ch)
                        <option value="{{ $ch->id }}" {{ in_array($ch->id, old('channels', [])) ? 'selected' : '' }}>
                            {{ $ch->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-neutral-500 mt-1">Segure Ctrl para selecionar múltiplos</p>
            </div>
        </div>

        <div class="flex gap-3">
            <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">
                Salvar Categoria
            </button>
            <a href="{{ route('admin.channel-categories.index') }}"
                class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">
                Cancelar
            </a>
        </div>
    </form>
</section>
@endsection

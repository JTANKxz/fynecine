@extends('layouts.admin')

@section('title', 'Editar Link do Canal')

@section('content')
    <section>
        <h2 class="text-xl font-bold mb-4">Editar Link: {{ $link->name }}</h2>

        <form action="{{ route('admin.channels.links.update', $link->id) }}" method="POST"
            class="bg-neutral-900 p-5 rounded space-y-4">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Nome</label>
                    <input type="text" name="name" value="{{ old('name', $link->name) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="Servidor 1">
                </div>

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Ordem</label>
                    <input type="number" name="order" value="{{ old('order', $link->order) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="1">
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm text-neutral-400 mb-1">Tipo</label>
                    <select name="type"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                        <option value="embed" {{ $link->type == 'embed' ? 'selected' : '' }}>Embed</option>
                        <option value="m3u8" {{ $link->type == 'm3u8' ? 'selected' : '' }}>M3U8</option>
                        <option value="custom" {{ $link->type == 'custom' ? 'selected' : '' }}>Custom (Sniffer)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm text-neutral-400 mb-1">URL</label>
                <input type="text" name="url" value="{{ old('url', $link->url) }}"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="https://player.com/embed/...">
            </div>

            <div>
                <label class="block text-sm text-neutral-400 mb-1">Subscription</label>
                <select name="player_sub"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none md:w-1/3">
                    <option value="free" {{ $link->player_sub == 'free' ? 'selected' : '' }}>Free</option>
                    <option value="premium" {{ $link->player_sub == 'premium' ? 'selected' : '' }}>Premium</option>
                </select>
            </div>

            <div class="flex gap-3">
                <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">
                    Atualizar Link
                </button>
                <a href="{{ route('admin.channels.links', $link->tv_channel_id) }}"
                    class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </section>
@endsection

@extends('layouts.admin')

@section('title', 'Edit Link')

@section('content')
<section>

    <h2 class="text-xl font-bold mb-4">Editar Link</h2>

    <form action="{{ route('admin.movies.links.update', $link->id) }}" method="POST"
        class="bg-neutral-900 p-5 rounded space-y-4">

        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-3 gap-4">

            <div>
                <label class="block text-sm text-neutral-400 mb-1">Nome</label>
                <input type="text" name="name" value="{{ $link->name }}"
                    class="w-full p-2 bg-neutral-800 rounded">
            </div>

            <div>
                <label class="block text-sm text-neutral-400 mb-1">Qualidade</label>
                <input type="text" name="quality" value="{{ $link->quality }}"
                    class="w-full p-2 bg-neutral-800 rounded">
            </div>

            <div>
                <label class="block text-sm text-neutral-400 mb-1">Order</label>
                <input type="number" name="order" value="{{ $link->order }}"
                    class="w-full p-2 bg-neutral-800 rounded">
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm text-neutral-400 mb-1">URL</label>
                <input type="text" name="url" value="{{ $link->url }}"
                    class="w-full p-2 bg-neutral-800 rounded">
            </div>

        </div>


        <div class="grid md:grid-cols-3 gap-4">

            <div>
                <label class="block text-sm text-neutral-400 mb-1">Tipo</label>
                <select name="type" class="w-full p-2 bg-neutral-800 rounded">

                    <option value="embed" {{ $link->type == 'embed' ? 'selected' : '' }}>Embed</option>
                    <option value="mp4" {{ $link->type == 'mp4' ? 'selected' : '' }}>MP4</option>
                    <option value="m3u8" {{ $link->type == 'm3u8' ? 'selected' : '' }}>M3U8</option>
                    <option value="mkv" {{ $link->type == 'mkv' ? 'selected' : '' }}>MKV</option>

                </select>
            </div>

            <div>
                <label class="block text-sm text-neutral-400 mb-1">Subscription</label>
                <select name="player_sub" class="w-full p-2 bg-neutral-800 rounded">

                    <option value="free" {{ $link->player_sub == 'free' ? 'selected' : '' }}>Free</option>
                    <option value="premium" {{ $link->player_sub == 'premium' ? 'selected' : '' }}>Premium</option>

                </select>
            </div>

        </div>


        <div class="flex gap-3">

            <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">
                Atualizar link
            </button>

            <a href="{{ route('admin.movies.links', $link->movie_id) }}"
                class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">
                Cancelar
            </a>

        </div>

    </form>

</section>
@endsection
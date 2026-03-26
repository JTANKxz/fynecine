@extends('layouts.admin')

@section('title', 'Create Link')

@section('content')
    <section>

        <h2 class="text-xl font-bold mb-4">Cadastrar Link</h2>

        <form action="{{ route('admin.movies.links.store', $movie->id) }}" method="POST"
            class="bg-neutral-900 p-5 rounded space-y-4">

            @csrf

            <div class="grid md:grid-cols-3 gap-4">

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Nome</label>
                    <input type="text" name="name"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="Servidor 1">
                </div>

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Qualidade</label>
                    <input type="text" name="quality"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="1080p">
                </div>

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Order</label>
                    <input type="number" name="order"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="1">
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm text-neutral-400 mb-1">URL</label>
                    <input type="text" name="url"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="https://player.com/embed/...">
                </div>

            </div>


            <div class="grid md:grid-cols-3 gap-4">

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Tipo</label>
                    <select name="type"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">

                        <option value="embed">Embed</option>
                        <option value="mp4">MP4</option>
                        <option value="m3u8">M3U8</option>
                        <option value="mkv">MKV</option>

                    </select>
                </div>

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Subscription</label>
                    <select name="player_sub"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">

                        <option value="free">Free</option>
                        <option value="premium">Premium</option>

                    </select>
                </div>

            </div>


            <div class="flex gap-3">

                <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">
                    Salvar link
                </button>

                <a href="{{ route('admin.movies.links', $movie->id) }}"
                    class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">
                    Cancelar
                </a>

            </div>

        </form>
    </section>
@endsection

@extends('layouts.admin')

@section('title', 'Editar Episode Link')

@section('content')
    <section>

        <h2 class="text-xl font-bold mb-4">
            Editar Link - Episódio {{ $link->episode->episode_number }}
        </h2>

        <form action="{{ route('admin.series.episodes.links.update', $link->id) }}" method="POST"
            class="bg-neutral-900 p-5 rounded space-y-4">

            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-3 gap-4">

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Nome</label>
                    <input type="text" name="name" value="{{ old('name', $link->name) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                </div>

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Qualidade</label>
                    <input type="text" name="quality" value="{{ old('quality', $link->quality) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                </div>

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Order</label>
                    <input type="number" name="order" value="{{ old('order', $link->order) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm text-neutral-400 mb-1">URL</label>
                    <input type="text" name="url" value="{{ old('url', $link->url) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                </div>

            </div>


            <div class="grid md:grid-cols-3 gap-4">

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Tipo</label>
                    <select name="type"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">

                        <option value="embed" {{ $link->type == 'embed' ? 'selected' : '' }}>Embed</option>
                        <option value="mp4" {{ $link->type == 'mp4' ? 'selected' : '' }}>MP4</option>
                        <option value="m3u8" {{ $link->type == 'm3u8' ? 'selected' : '' }}>M3U8</option>
                        <option value="mkv" {{ $link->type == 'mkv' ? 'selected' : '' }}>MKV</option>
                        <option value="custom" {{ $link->type == 'custom' ? 'selected' : '' }}>Custom (Sniffer)</option>
                        <option value="private" {{ $link->type == 'private' ? 'selected' : '' }}>Private (Bunny CDN)</option>

                    </select>
                </div>

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Subscription</label>
                    <select name="player_sub"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">

                        <option value="free" {{ $link->player_sub == 'free' ? 'selected' : '' }}>Free</option>
                        <option value="premium" {{ $link->player_sub == 'premium' ? 'selected' : '' }}>Premium</option>

                    </select>
            </div>

            {{-- BUNNY CDN SETTINGS --}}
            <div class="grid md:grid-cols-2 gap-4 p-4 border border-dashed border-neutral-700 bg-neutral-800/30 rounded">
                <div class="md:col-span-2">
                    <h3 class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-2">Bunny CDN (Private Only)</h3>
                </div>
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Path do Vídeo</label>
                    <input type="text" name="link_path" value="{{ old('link_path', $link->link_path) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="Ex: /d478e2ca-f254.../">
                </div>
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Expiração (Horas)</label>
                    <input type="number" name="expiration_hours" value="{{ old('expiration_hours', $link->expiration_hours ?? 4) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                </div>
            </div>

            {{-- HTTP HEADERS --}}
            <div class="grid md:grid-cols-2 gap-4 p-4 border border-dashed border-neutral-700 bg-neutral-800/20 rounded">
                <div class="md:col-span-2">
                    <h3 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-2">HTTP Headers & Cookies (Optional)</h3>
                </div>
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">User Agent</label>
                    <input type="text" name="user_agent" value="{{ old('user_agent', $link->user_agent) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="ExoPlayer">
                </div>
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Referer</label>
                    <input type="text" name="referer" value="{{ old('referer', $link->referer) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="https://meusite.com">
                </div>
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Origin</label>
                    <input type="text" name="origin" value="{{ old('origin', $link->origin) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="https://meusite.com">
                </div>
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Cookie</label>
                    <input type="text" name="cookie" value="{{ old('cookie', $link->cookie) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="key=value; key2=value2">
                </div>
            </div>

            {{-- SKIP INTRO --}}
            <div class="grid md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        Skip Intro Start (mm:ss)
                    </label>

                    <input type="text" name="skip_intro_start"
                        value="{{ old('skip_intro_start', $link->skip_intro_start_time) }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="00:00">
                </div>

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        Skip Intro End (mm:ss)
                    </label>

                    <input type="text" name="skip_intro_end"
                        value="{{ old('skip_intro_end', $link->skip_intro_end ? gmdate('i:s', $link->skip_intro_end) : '') }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="01:30">
                </div>

            </div>


            {{-- SKIP ENDING --}}
            <div class="grid md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        Skip Ending Start (mm:ss)
                    </label>

                    <input type="text" name="skip_ending_start"
                        value="{{ old('skip_ending_start', $link->skip_ending_start ? gmdate('i:s', $link->skip_ending_start) : '') }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="21:40">
                </div>

                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        Skip Ending End (mm:ss)
                    </label>

                    <input type="text" name="skip_ending_end"
                        value="{{ old('skip_ending_end', $link->skip_ending_end ? gmdate('i:s', $link->skip_ending_end) : '') }}"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="22:10">
                </div>

            </div>


            <div class="flex gap-3">

                <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">
                    Atualizar link
                </button>

                <a href="{{ route('admin.series.episodes.links', $link->episode_id) }}"
                    class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">
                    Cancelar
                </a>

            </div>

        </form>

    </section>
@endsection

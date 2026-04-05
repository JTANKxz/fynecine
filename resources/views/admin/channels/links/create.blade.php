@extends('layouts.admin')

@section('title', 'Criar Link do Canal')

@section('content')
    <section>
        <h2 class="text-xl font-bold mb-4">Novo Link: {{ $channel->name }}</h2>

        <form action="{{ route('admin.channels.links.store', $channel->id) }}" method="POST"
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
                    <label class="block text-sm text-neutral-400 mb-1">Ordem</label>
                    <input type="number" name="order"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="1">
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm text-neutral-400 mb-1">Tipo</label>
                    <select name="type"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                        <option value="embed">Embed</option>
                        <option value="m3u8">M3U8</option>
                        <option value="custom">Custom (Sniffer)</option>
                        <option value="private">Private (Bunny HLS)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm text-neutral-400 mb-1">URL</label>
                <input type="text" name="url"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="https://player.com/embed/... ou URL m3u8">
            </div>

            {{-- Bunny fields --}}
            <div id="bunny_fields" class="hidden grid md:grid-cols-2 gap-4 border-t border-neutral-800 pt-4">
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Link Path (opcional)</label>
                    <input type="text" name="link_path" class="w-full p-2 bg-neutral-800 rounded outline-none" placeholder="/video-folder/">
                </div>
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Expiração (horas)</label>
                    <input type="number" name="expiration_hours" value="4" class="w-full p-2 bg-neutral-800 rounded outline-none">
                </div>
            </div>

            {{-- Header fields --}}
            <div class="border-t border-neutral-800 pt-4">
                <h3 class="text-sm font-bold text-neutral-500 mb-3 uppercase tracking-wider">Configurações Avançadas (Headers)</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-neutral-400 mb-1">User-Agent</label>
                        <input type="text" name="user_agent" class="w-full p-2 bg-neutral-800 rounded outline-none" placeholder="ExoPlayer/2.18.1">
                    </div>
                    <div>
                        <label class="block text-sm text-neutral-400 mb-1">Referer</label>
                        <input type="text" name="referer" class="w-full p-2 bg-neutral-800 rounded outline-none" placeholder="https://site.com">
                    </div>
                    <div>
                        <label class="block text-sm text-neutral-400 mb-1">Origin</label>
                        <input type="text" name="origin" class="w-full p-2 bg-neutral-800 rounded outline-none" placeholder="https://site.com">
                    </div>
                    <div>
                        <label class="block text-sm text-neutral-400 mb-1">Cookies</label>
                        <input type="text" name="cookie" class="w-full p-2 bg-neutral-800 rounded outline-none" placeholder="key=value; key2=value2">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm text-neutral-400 mb-1">Subscription</label>
                <select name="player_sub"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none md:w-1/3">
                    <option value="free">Free</option>
                    <option value="premium">Premium</option>
                </select>
            </div>

            <div class="flex gap-3">
                <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">
                    Salvar Link
                </button>
                <a href="{{ route('admin.channels.links', $channel->id) }}"
                    class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </section>

    <script>
        document.querySelector('select[name="type"]').addEventListener('change', function() {
            const bunnyFields = document.getElementById('bunny_fields');
            if (this.value === 'private') {
                bunnyFields.classList.remove('hidden');
            } else {
                bunnyFields.classList.add('hidden');
            }
        });
    </script>
@endsection

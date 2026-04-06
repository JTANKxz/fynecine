@extends('layouts.admin')

@section('title', 'Adicionar Link de Evento')

@section('content')
<section class="max-w-2xl">
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('admin.events.links', $event->id) }}" class="w-10 h-10 bg-neutral-900 border border-neutral-800 rounded-full flex items-center justify-center hover:bg-neutral-800 transition text-white">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-white">Adicionar Novo Link</h2>
            <p class="text-sm text-neutral-500">Evento: <span class="text-netflix font-bold">{{ $event->title }}</span></p>
        </div>
    </div>

    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-6 md:p-8 shadow-2xl">
        <form action="{{ route('admin.events.links.store', $event->id) }}" method="POST" class="space-y-6">
            @csrf

            {{-- Nome do Player --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Nome do Player</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ex: Principal HD, Opção 2 SD"
                    class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition @error('name') border-red-500 @enderror">
                @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            {{-- URL --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">URL do Stream</label>
                <input type="text" name="url" value="{{ old('url') }}" placeholder="https://..."
                    class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition font-mono text-sm @error('url') border-red-500 @enderror">
                @error('url') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Tipo --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Tipo de Link</label>
                    <select name="type" id="type_select" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition cursor-pointer">
                        <option value="embed" {{ old('type') == 'embed' ? 'selected' : '' }}>Embed (Iframe/Webview)</option>
                        <option value="direct" {{ old('type', 'direct') == 'direct' ? 'selected' : '' }}>Direct (Player Interno)</option>
                        <option value="m3u8" {{ old('type') == 'm3u8' ? 'selected' : '' }}>M3U8 (HLS)</option>
                        <option value="mp4" {{ old('type') == 'mp4' ? 'selected' : '' }}>MP4</option>
                        <option value="mkv" {{ old('type') == 'mkv' ? 'selected' : '' }}>MKV</option>
                        <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>Custom (Sniffer)</option>
                        <option value="private" {{ old('type') == 'private' ? 'selected' : '' }}>Private (Bunny CDN)</option>
                    </select>
                </div>

                {{-- Player Sub --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Nível de Acesso</label>
                    <select name="player_sub" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition cursor-pointer">
                        <option value="free" {{ old('player_sub') == 'free' ? 'selected' : '' }}>Livre (Free)</option>
                        <option value="premium" {{ old('player_sub', 'premium') == 'premium' ? 'selected' : '' }}>Somente VIP / Premium</option>
                    </select>
                </div>
            </div>

            {{-- Bunny fields --}}
            <div id="bunny_fields" class="{{ old('type') == 'private' ? '' : 'hidden' }} grid md:grid-cols-2 gap-6 border-t border-neutral-800 pt-6">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px] text-purple-400">Link Path (Bunny CDN)</label>
                    <input type="text" name="link_path" value="{{ old('link_path') }}" class="w-full bg-black border border-purple-900/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition" placeholder="/video-folder/">
                    @error('link_path') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px] text-purple-400">Expiração (horas)</label>
                    <input type="number" name="expiration_hours" value="{{ old('expiration_hours', 4) }}" class="w-full bg-black border border-purple-900/30 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">
                    @error('expiration_hours') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Header fields --}}
            <div class="border-t border-neutral-800 pt-6">
                <h3 class="text-xs font-bold text-blue-500 mb-4 uppercase tracking-[0.2em] flex items-center gap-2">
                    <i class="fa-solid fa-gears"></i> Configurações de Headers
                </h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-neutral-500 uppercase">User-Agent</label>
                        <input type="text" name="user_agent" value="{{ old('user_agent') }}" class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-2 text-white text-sm focus:border-netflix outline-none" placeholder="ExoPlayer/2.18.1">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-neutral-500 uppercase">Referer</label>
                        <input type="text" name="referer" value="{{ old('referer') }}" class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-2 text-white text-sm focus:border-netflix outline-none" placeholder="https://site.com">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-neutral-500 uppercase">Origin</label>
                        <input type="text" name="origin" value="{{ old('origin') }}" class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-2 text-white text-sm focus:border-netflix outline-none" placeholder="https://site.com">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-[10px] font-bold text-neutral-500 uppercase">Cookies</label>
                        <input type="text" name="cookie" value="{{ old('cookie') }}" class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-2 text-white text-sm focus:border-netflix outline-none" placeholder="key=value;">
                    </div>
                </div>
            </div>

            <div class="border-t border-neutral-800 pt-6">
                <button type="submit" class="w-full bg-netflix hover:bg-red-700 text-white font-black px-10 py-4 rounded-xl shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-save mr-1"></i> SALVAR LINK
                </button>
            </div>
        </form>
    </div>
</section>

@push('scripts')
<script>
    document.getElementById('type_select').addEventListener('change', function() {
        const bunnyFields = document.getElementById('bunny_fields');
        if (this.value === 'private') {
            bunnyFields.classList.remove('hidden');
        } else {
            bunnyFields.classList.add('hidden');
        }
    });
</script>
@endpush
@endsection

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
        <form action="{{ route('events.links.store', $event->id) }}" method="POST" class="space-y-6">
            @csrf

            {{-- Nome do Player --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Nome do Player</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ex: Principal HD, Opção 2 SD"
                    class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">
            </div>

            {{-- URL --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">URL do Stream</label>
                <input type="text" name="url" value="{{ old('url') }}" required placeholder="https://..."
                    class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition font-mono text-sm">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Tipo --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Tipo de Link</label>
                    <select name="type" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition cursor-pointer">
                        <option value="embed">Embed (Iframe/Webview)</option>
                        <option value="direct" selected>Direct (Player Interno)</option>
                        <option value="m3u8">M3U8 (HLS)</option>
                        <option value="mp4">MP4</option>
                        <option value="mkv">MKV</option>
                    </select>
                </div>

                {{-- Player Sub --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Nível de Acesso</label>
                    <select name="player_sub" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition cursor-pointer">
                        <option value="free">Livre (Free)</option>
                        <option value="premium" selected>Somente VIP / Premium</option>
                    </select>
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
@endsection

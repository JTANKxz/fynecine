@extends('layouts.admin')

@section('title', 'Gerenciar Links - '.$serie->name)

@section('content')
<section>
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.links.series') }}" class="bg-neutral-900 border border-neutral-800 p-2 rounded hover:bg-neutral-800">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold">Gerenciador de Links: {{ $serie->name }}</h2>
                <p class="text-xs text-neutral-500">Configure os links de cada episódio desta série. <span class="text-netflix font-bold">(Links MP4 são assinados automaticamente via BunnyCDN)</span></p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-900 border border-green-600 text-green-100 px-4 py-2 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6">
        @foreach($serie->seasons as $season)
            <div class="bg-neutral-900 border border-neutral-800 rounded-lg overflow-hidden">
                <div class="bg-neutral-800/50 p-4 border-b border-neutral-800 flex justify-between items-center cursor-pointer" onclick="toggleSeason({{ $season->id }})">
                    <h3 class="font-bold text-white uppercase tracking-tighter">Temporada {{ $season->season_number }}</h3>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform" id="arrow-season-{{ $season->id }}"></i>
                </div>
                
                <div id="season-{{ $season->id }}" class="hidden p-4 space-y-8 bg-black/20">
                    @foreach($season->episodes as $episode)
                        <div class="border-l-2 border-netflix pl-6 py-2">
                            <div class="flex items-center justify-between flex-wrap gap-4 mb-4">
                                <div>
                                    <h4 class="font-bold text-neutral-100">Episódio {{ $episode->episode_number }}: {{ $episode->name }}</h4>
                                    <p class="text-[10px] text-neutral-500 uppercase">Configuração de Players</p>
                                </div>
                                
                                {{-- Form Novo Link Ep --}}
                                <form action="{{ route('admin.links.series.episode.store', $episode->id) }}" method="POST" class="bg-neutral-900 border border-neutral-800 p-3 rounded-lg flex flex-col gap-3">
                                    @csrf
                                    <div class="flex flex-wrap items-center gap-3">
                                        <div class="flex flex-col">
                                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Servidor (Ex: Netu)" required class="bg-neutral-800 border-none rounded px-3 py-1.5 text-xs w-32 outline-none @error('name') ring-1 ring-red-500 @enderror">
                                            @error('name') <span class="text-[8px] text-red-500 mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <div class="flex flex-col">
                                            <input type="text" name="url" value="{{ old('url') }}" placeholder="URL do Link" class="bg-neutral-800 border-none rounded px-3 py-1.5 text-xs w-48 outline-none @error('url') ring-1 ring-red-500 @enderror">
                                            @error('url') <span class="text-[8px] text-red-500 mt-0.5">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="flex flex-col">
                                            <input type="text" name="quality" value="{{ old('quality', '1080p') }}" placeholder="Qualidade" class="bg-neutral-800 border-none rounded px-3 py-1.5 text-xs w-24 outline-none">
                                        </div>
                                        
                                        <div class="flex gap-2">
                                            <select name="type" class="bg-neutral-800 rounded text-xs py-1.5 px-2 outline-none border-none">
                                                <option value="embed" {{ old('type') == 'embed' ? 'selected' : '' }}>EMBED</option>
                                                <option value="mp4" {{ old('type') == 'mp4' ? 'selected' : '' }}>MP4</option>
                                                <option value="m3u8" {{ old('type') == 'm3u8' ? 'selected' : '' }}>M3U8</option>
                                                <option value="custom" {{ old('type') == 'custom' ? 'selected' : '' }}>CUSTOM</option>
                                                <option value="private" {{ old('type') == 'private' ? 'selected' : '' }}>PRIVATE</option>
                                            </select>
                                            <div class="bunny-fields {{ old('type') == 'private' ? '' : 'hidden' }} flex gap-1">
                                                <input type="text" name="link_path" value="{{ old('link_path') }}" placeholder="Path (opcional)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-24">
                                                <input type="number" name="expiration_hours" value="{{ old('expiration_hours', 4) }}" placeholder="Exp (h)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-12">
                                            </div>
                                            <select name="player_sub" class="bg-neutral-800 rounded text-xs py-1.5 px-2 outline-none border-none text-yellow-500 font-bold">
                                                <option value="free" {{ old('player_sub') == 'free' ? 'selected' : '' }}>FREE</option>
                                                <option value="premium" {{ old('player_sub', 'premium') == 'premium' ? 'selected' : '' }}>VIP</option>
                                            </select>
                                        </div>

                                        <button type="button" onclick="toggleAdvanced(this)" class="bg-neutral-800 text-neutral-400 px-2 py-1 rounded text-[10px] hover:bg-neutral-700 transition">
                                            <i class="fa-solid fa-gears mr-1"></i> AVANÇADO
                                        </button>

                                        <button type="submit" class="bg-netflix text-white px-4 py-1.5 rounded text-xs font-bold hover:bg-red-700 transition shadow-lg">
                                            + ADICIONAR
                                        </button>
                                    </div>

                                    <div class="advanced-fields hidden grid grid-cols-2 lg:grid-cols-4 gap-2 pt-2 border-t border-neutral-800/50">
                                        <input type="text" name="subtitle_url" value="{{ old('subtitle_url') }}" placeholder="URL da Legenda (.vtt/.srt)" class="col-span-full bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none mb-1">
                                        <input type="text" name="user_agent" value="{{ old('user_agent') }}" placeholder="User-Agent" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                        <input type="text" name="referer" value="{{ old('referer') }}" placeholder="Referer" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                        <input type="text" name="origin" value="{{ old('origin') }}" placeholder="Origin" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                        <input type="text" name="cookie" value="{{ old('cookie') }}" placeholder="Cookies" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                    </div>

                                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
                                        <div class="flex flex-col">
                                            <input type="text" name="skip_intro_start" value="{{ old('skip_intro_start') }}" placeholder="Intro Início (mm:ss)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-full outline-none">
                                            @error('skip_intro_start') <span class="text-[7px] text-red-500 mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="flex flex-col">
                                            <input type="text" name="skip_intro_end" value="{{ old('skip_intro_end') }}" placeholder="Intro Fim (mm:ss)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-full outline-none">
                                            @error('skip_intro_end') <span class="text-[7px] text-red-500 mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="flex flex-col">
                                            <input type="text" name="skip_ending_start" value="{{ old('skip_ending_start') }}" placeholder="Fim Início (mm:ss)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-full outline-none">
                                            @error('skip_ending_start') <span class="text-[7px] text-red-500 mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="flex flex-col">
                                            <input type="text" name="skip_ending_end" value="{{ old('skip_ending_end') }}" placeholder="Fim Fim (mm:ss)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-full outline-none">
                                            @error('skip_ending_end') <span class="text-[7px] text-red-500 mt-0.5">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </form>
                            </div>

                            {{-- Listagem de Links do Ep --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @forelse($episode->links as $link)
                                    <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-lg relative group border-t-2 {{ $link->player_sub == 'premium' ? 'border-yellow-600' : 'border-blue-600' }}">
                                        <form action="{{ route('admin.links.series.episode.update', $link->id) }}" method="POST" class="space-y-4">
                                            @csrf @method('PUT')
                                            
                                            <div class="flex justify-between items-center">
                                                <input type="text" name="name" value="{{ old('name', $link->name) }}" class="bg-transparent border-none p-0 text-sm font-bold text-white outline-none w-32 focus:ring-1 focus:ring-netflix rounded">
                                                <div class="flex gap-2">
                                                    <button type="submit" title="Salvar" class="text-blue-500 hover:text-white transition"><i class="fa-solid fa-floppy-disk"></i></button>
                                                    <button type="button" onclick="deleteLink({{ $link->id }})" title="Excluir" class="text-red-500 hover:text-white transition"><i class="fa-solid fa-trash-can"></i></button>
                                                </div>
                                            </div>

                                            <input type="text" name="url" value="{{ old('url', $link->url) }}" class="w-full bg-neutral-800 border-none rounded px-2 py-1.5 text-xs text-neutral-400 font-mono italic outline-none focus:ring-1 focus:ring-netflix">
                                            
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-[9px] text-neutral-500 uppercase font-bold mb-1">Qualidade / Tipo</label>
                                                    <div class="flex gap-2">
                                                        <input type="text" name="quality" value="{{ old('quality', $link->quality) }}" placeholder="Qualidade" class="w-1/2 bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                                        <select name="type" class="w-1/2 bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                                            <option value="embed" {{ old('type', $link->type) == 'embed' ? 'selected' : '' }}>EMBED</option>
                                                            <option value="mp4" {{ old('type', $link->type) == 'mp4' ? 'selected' : '' }}>MP4</option>
                                                            <option value="m3u8" {{ old('type', $link->type) == 'm3u8' ? 'selected' : '' }}>M3U8</option>
                                                            <option value="custom" {{ old('type', $link->type) == 'custom' ? 'selected' : '' }}>CUSTOM</option>
                                                            <option value="private" {{ old('type', $link->type) == 'private' ? 'selected' : '' }}>PRIVATE</option>
                                                        </select>
                                                    </div>
                                                    <div class="bunny-fields {{ old('type', $link->type) == 'private' ? '' : 'hidden' }} flex gap-1 mt-1">
                                                        <input type="text" name="link_path" value="{{ old('link_path', $link->link_path) }}" placeholder="Path" class="w-1/2 bg-black/40 border-none rounded px-2 py-1 text-[8px] text-neutral-400">
                                                        <input type="number" name="expiration_hours" value="{{ old('expiration_hours', $link->expiration_hours ?? 4) }}" placeholder="Hs" class="w-1/4 bg-black/40 border-none rounded px-2 py-1 text-[8px] text-neutral-400">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-[9px] text-neutral-500 uppercase font-bold mb-1">Assinatura / Ordem</label>
                                                    <div class="flex gap-2">
                                                        <select name="player_sub" class="w-1/2 bg-neutral-800 border-none rounded px-2 py-1 text-[10px] {{ $link->player_sub == 'premium' ? 'text-yellow-500' : 'text-blue-500' }} outline-none">
                                                            <option value="free" {{ old('player_sub', $link->player_sub) == 'free' ? 'selected' : '' }}>FREE</option>
                                                            <option value="premium" {{ old('player_sub', $link->player_sub) == 'premium' ? 'selected' : '' }}>PREMIUM</option>
                                                        </select>
                                                        <input type="number" name="order" value="{{ old('order', $link->order) }}" placeholder="Ordem" class="w-1/2 bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 pt-1 border-t border-neutral-800/50">
                                                <button type="button" onclick="toggleAdvanced(this)" class="text-[8px] text-neutral-500 hover:text-white uppercase font-bold">
                                                    <i class="fa-solid fa-gears mr-1"></i> AVANÇADO
                                                </button>
                                            </div>

                                            <div class="advanced-fields hidden space-y-2 p-2 bg-black/30 rounded">
                                                <input type="text" name="subtitle_url" value="{{ old('subtitle_url', $link->subtitle_url) }}" placeholder="URL da Legenda (.vtt/.srt)" class="w-full bg-neutral-800 border-none rounded px-2 py-1 text-[9px] text-neutral-400 outline-none mb-1">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="text" name="user_agent" value="{{ old('user_agent', $link->user_agent) }}" placeholder="User-Agent" class="bg-neutral-800 border-none rounded px-2 py-1 text-[9px] text-neutral-400 outline-none">
                                                    <input type="text" name="referer" value="{{ old('referer', $link->referer) }}" placeholder="Referer" class="bg-neutral-800 border-none rounded px-2 py-1 text-[9px] text-neutral-400 outline-none">
                                                    <input type="text" name="origin" value="{{ old('origin', $link->origin) }}" placeholder="Origin" class="bg-neutral-800 border-none rounded px-2 py-1 text-[9px] text-neutral-400 outline-none">
                                                    <input type="text" name="cookie" value="{{ old('cookie', $link->cookie) }}" placeholder="Cookies" class="bg-neutral-800 border-none rounded px-2 py-1 text-[9px] text-neutral-400 outline-none">
                                                </div>

                                                <div class="grid grid-cols-2 gap-2 mt-2">
                                                    <div>
                                                        <label class="block text-[8px] text-neutral-600 uppercase font-bold mb-1">Abertura (mm:ss)</label>
                                                        <div class="flex gap-1">
                                                            <input type="text" name="skip_intro_start" value="{{ old('skip_intro_start', $link->skip_intro_start ? gmdate('i:s', $link->skip_intro_start) : '') }}" placeholder="Início" class="bg-neutral-800 border-none rounded px-1.5 py-1 text-[8px] w-full outline-none">
                                                            <input type="text" name="skip_intro_end" value="{{ old('skip_intro_end', $link->skip_intro_end ? gmdate('i:s', $link->skip_intro_end) : '') }}" placeholder="Fim" class="bg-neutral-800 border-none rounded px-1.5 py-1 text-[8px] w-full outline-none">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-[8px] text-neutral-600 uppercase font-bold mb-1">Encerramento (mm:ss)</label>
                                                        <div class="flex gap-1">
                                                            <input type="text" name="skip_ending_start" value="{{ old('skip_ending_start', $link->skip_ending_start ? gmdate('i:s', $link->skip_ending_start) : '') }}" placeholder="Início" class="bg-neutral-800 border-none rounded px-1.5 py-1 text-[8px] w-full outline-none">
                                                            <input type="text" name="skip_ending_end" value="{{ old('skip_ending_end', $link->skip_ending_end ? gmdate('i:s', $link->skip_ending_end) : '') }}" placeholder="Fim" class="bg-neutral-800 border-none rounded px-1.5 py-1 text-[8px] w-full outline-none">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>

                                        <form id="delete-link-{{ $link->id }}" action="{{ route('admin.links.series.episode.delete', $link->id) }}" method="POST" class="hidden">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                @empty
                                    <div class="col-span-full border border-dashed border-neutral-800 p-4 text-center text-[10px] text-neutral-600 italic">
                                        Nenhum player configurado para este episódio.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</section>

<script>
    function toggleSeason(id) {
        const content = document.getElementById(`season-${id}`);
        const arrow = document.getElementById(`arrow-season-${id}`);
        content.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
    }

    function deleteLink(id) {
        if(confirm('Deseja realmente remover este player?')) {
            document.getElementById(`delete-link-${id}`).submit();
        }
    }

    function toggleAdvanced(btn) {
        const form = btn.closest('form');
        const advancedFields = form.querySelector('.advanced-fields');
        if (advancedFields) {
            advancedFields.classList.toggle('hidden');
        }
    }

    document.querySelectorAll('select[name="type"]').forEach(select => {
        select.addEventListener('change', function() {
            const form = this.closest('form');
            const bunnyFields = form.querySelector('.bunny-fields');
            if (bunnyFields) {
                if (this.value === 'private') {
                    bunnyFields.classList.remove('hidden');
                } else {
                    bunnyFields.classList.add('hidden');
                }
            }
        });
    });
</script>
@endsection

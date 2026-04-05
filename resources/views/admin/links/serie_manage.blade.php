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
                <p class="text-xs text-neutral-500">Configure os links de cada episódio desta série.</p>
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
                                        <input type="text" name="name" placeholder="Servidor (Ex: Netu)" required class="bg-neutral-800 border-none rounded px-3 py-1.5 text-xs w-32 outline-none">
                                        <input type="text" name="url" placeholder="URL do Link" required class="bg-neutral-800 border-none rounded px-3 py-1.5 text-xs w-48 outline-none">
                                        <input type="text" name="quality" placeholder="Qualidade (Ex: 1080p)" class="bg-neutral-800 border-none rounded px-3 py-1.5 text-xs w-24 outline-none">
                                        
                                        <div class="flex gap-2">
                                            <select name="type" class="bg-neutral-800 rounded text-xs py-1.5 px-2 outline-none border-none">
                                                <option value="embed">EMBED</option>
                                                <option value="mp4">MP4</option>
                                                <option value="m3u8">M3U8</option>
                                                <option value="custom">CUSTOM</option>
                                                <option value="private">PRIVATE</option>
                                            </select>
                                            <div class="bunny-fields hidden flex gap-1">
                                                <input type="text" name="link_path" placeholder="Path (opcional)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-24">
                                                <input type="number" name="expiration_hours" value="4" placeholder="Exp (h)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-12">
                                            </div>
                                            <select name="player_sub" class="bg-neutral-800 rounded text-xs py-1.5 px-2 outline-none border-none text-yellow-500 font-bold">
                                                <option value="free">FREE</option>
                                                <option value="premium">VIP</option>
                                            </select>
                                        </div>

                                        <button type="button" onclick="toggleAdvanced(this)" class="bg-neutral-800 text-neutral-400 px-2 py-1.5 rounded text-[10px] hover:bg-neutral-700 transition">
                                            <i class="fa-solid fa-gears mr-1"></i> HEADERS
                                        </button>

                                        <button type="submit" class="bg-netflix text-white px-4 py-1.5 rounded text-xs font-bold hover:bg-red-700 transition shadow-lg">
                                            + ADICIONAR
                                        </button>
                                    </div>

                                    <div class="advanced-fields hidden grid grid-cols-2 lg:grid-cols-4 gap-2 pt-2 border-t border-neutral-800/50">
                                        <input type="text" name="user_agent" placeholder="User-Agent" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                        <input type="text" name="referer" placeholder="Referer" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                        <input type="text" name="origin" placeholder="Origin" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                        <input type="text" name="cookie" placeholder="Cookies" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px] outline-none">
                                    </div>

                                    <div class="grid grid-cols-2 gap-2">
                                        <input type="number" name="skip_intro_start" placeholder="Skip Intro Início (s)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-28">
                                        <input type="number" name="skip_intro_end" placeholder="Skip Intro Fim (s)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-28">
                                        <input type="number" name="skip_ending_start" placeholder="Skip Encerramento Início (s)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-28">
                                        <input type="number" name="skip_ending_end" placeholder="Skip Encerramento Fim (s)" class="bg-neutral-800 rounded px-2 py-1 text-[9px] w-28">
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
                                                <input type="text" name="name" value="{{ $link->name }}" class="bg-transparent border-none p-0 text-sm font-bold text-white outline-none w-32">
                                                <div class="flex gap-2">
                                                    <button type="submit" title="Salvar" class="text-blue-500 hover:text-white transition"><i class="fa-solid fa-floppy-disk"></i></button>
                                                    <button type="button" onclick="deleteLink({{ $link->id }})" title="Excluir" class="text-red-500 hover:text-white transition"><i class="fa-solid fa-trash-can"></i></button>
                                                </div>
                                            </div>

                                            <input type="text" name="url" value="{{ $link->url }}" class="w-full bg-neutral-800 border-none rounded px-2 py-1.5 text-xs text-neutral-400 font-mono italic">
                                            
                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-[9px] text-neutral-500 uppercase font-bold mb-1">Qualidade / Tipo</label>
                                                    <div class="flex gap-2">
                                                        <input type="text" name="quality" value="{{ $link->quality }}" placeholder="Qualidade" class="w-1/2 bg-neutral-800 border-none rounded px-2 py-1 text-[10px]">
                                                        <select name="type" class="w-1/2 bg-neutral-800 border-none rounded px-2 py-1 text-[10px]">
                                                            <option value="embed" {{ $link->type == 'embed' ? 'selected' : '' }}>EMBED</option>
                                                            <option value="mp4" {{ $link->type == 'mp4' ? 'selected' : '' }}>MP4</option>
                                                            <option value="m3u8" {{ $link->type == 'm3u8' ? 'selected' : '' }}>M3U8</option>
                                                            <option value="custom" {{ $link->type == 'custom' ? 'selected' : '' }}>CUSTOM</option>
                                                            <option value="private" {{ $link->type == 'private' ? 'selected' : '' }}>PRIVATE</option>
                                                        </select>
                                                    </div>
                                                    @if($link->type == 'private')
                                                    <div class="flex gap-1 mt-1">
                                                        <input type="text" name="link_path" value="{{ $link->link_path }}" placeholder="Path" class="w-1/2 bg-black/40 border-none rounded px-2 py-1 text-[8px] text-neutral-400">
                                                        <input type="number" name="expiration_hours" value="{{ $link->expiration_hours }}" placeholder="Hs" class="w-1/4 bg-black/40 border-none rounded px-2 py-1 text-[8px] text-neutral-400">
                                                    </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <label class="block text-[9px] text-neutral-500 uppercase font-bold mb-1">Assinatura / Ordem</label>
                                                    <div class="flex gap-2">
                                                        <select name="player_sub" class="w-1/2 bg-neutral-800 border-none rounded px-2 py-1 text-[10px] {{ $link->player_sub == 'premium' ? 'text-yellow-500' : 'text-blue-500' }}">
                                                            <option value="free" {{ $link->player_sub == 'free' ? 'selected' : '' }}>FREE</option>
                                                            <option value="premium" {{ $link->player_sub == 'premium' ? 'selected' : '' }}>PREMIUM</option>
                                                        </select>
                                                        <input type="number" name="order" value="{{ $link->order }}" placeholder="Ordem" class="w-1/2 bg-neutral-800 border-none rounded px-2 py-1 text-[10px]">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 pt-1 border-t border-neutral-800/50">
                                                <button type="button" onclick="toggleAdvanced(this)" class="text-[8px] text-neutral-500 hover:text-white uppercase font-bold">
                                                    <i class="fa-solid fa-gears mr-1"></i> Headers
                                                </button>
                                            </div>

                                            <div class="advanced-fields hidden space-y-2 p-2 bg-black/30 rounded">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="text" name="user_agent" value="{{ $link->user_agent }}" placeholder="User-Agent" class="bg-neutral-800 border-none rounded px-2 py-1 text-[9px] text-neutral-400">
                                                    <input type="text" name="referer" value="{{ $link->referer }}" placeholder="Referer" class="bg-neutral-800 border-none rounded px-2 py-1 text-[9px] text-neutral-400">
                                                    <input type="text" name="origin" value="{{ $link->origin }}" placeholder="Origin" class="bg-neutral-800 border-none rounded px-2 py-1 text-[9px] text-neutral-400">
                                                    <input type="text" name="cookie" value="{{ $link->cookie }}" placeholder="Cookies" class="bg-neutral-800 border-none rounded px-2 py-1 text-[9px] text-neutral-400">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4 p-2 bg-black/30 rounded">
                                                <div>
                                                    <label class="block text-[8px] text-neutral-600 uppercase font-bold mb-1">Abertura / Encerramento (s)</label>
                                                    <div class="flex gap-1">
                                                        <input type="number" name="skip_intro_start" value="{{ $link->skip_intro_start }}" placeholder="Início" class="bg-neutral-800 border-none rounded px-1.5 py-1 text-[8px] w-full">
                                                        <input type="number" name="skip_intro_end" value="{{ $link->skip_intro_end }}" placeholder="Fim" class="bg-neutral-800 border-none rounded px-1.5 py-1 text-[8px] w-full">
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-[8px] text-neutral-600 uppercase font-bold mb-1">Encerramento (s)</label>
                                                    <div class="flex gap-1">
                                                        <input type="number" name="skip_ending_start" value="{{ $link->skip_ending_start }}" placeholder="Início" class="bg-neutral-800 border-none rounded px-1.5 py-1 text-[8px] w-full">
                                                        <input type="number" name="skip_ending_end" value="{{ $link->skip_ending_end }}" placeholder="Fim" class="bg-neutral-800 border-none rounded px-1.5 py-1 text-[8px] w-full">
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

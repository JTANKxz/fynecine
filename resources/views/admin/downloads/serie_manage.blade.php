@extends('layouts.admin')

@section('title', 'Downloads - '.$serie->name)

@section('content')
<section>
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.downloads.series') }}" class="bg-neutral-900 border border-neutral-800 p-2 rounded hover:bg-neutral-800">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold">Downloads: {{ $serie->name }}</h2>
                <p class="text-xs text-neutral-500">Configure os links de download de cada episódio.</p>
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
                <div class="bg-neutral-800/50 p-4 border-b border-neutral-800 flex justify-between items-center cursor-pointer"
                     onclick="toggleSeason({{ $season->id }})">
                    <h3 class="font-bold text-white uppercase tracking-tighter">
                        <i class="fa-solid fa-download mr-2 text-green-500 text-xs"></i>
                        Temporada {{ $season->season_number }}
                    </h3>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform" id="arrow-season-{{ $season->id }}"></i>
                </div>

                <div id="season-{{ $season->id }}" class="hidden p-4 space-y-8 bg-black/20">
                    @foreach($season->episodes as $episode)
                        <div class="border-l-2 border-green-700 pl-6 py-2">
                            <div class="flex items-start justify-between flex-wrap gap-4 mb-4">
                                <div>
                                    <h4 class="font-bold text-neutral-100">
                                        Ep {{ $episode->episode_number }}: {{ $episode->name }}
                                    </h4>
                                    <p class="text-[10px] text-neutral-500 uppercase">Links de Download</p>
                                </div>

                                {{-- Form Adicionar Download do Episódio --}}
                                <form action="{{ route('admin.downloads.series.episode.store', $episode->id) }}" method="POST"
                                      class="bg-neutral-900 border border-neutral-800 p-3 rounded-lg flex flex-wrap items-center gap-3">
                                    @csrf
                                    <input type="text" name="name" placeholder="Nome (ex: Download FHD)" required
                                           class="bg-neutral-800 border-none rounded px-3 py-1.5 text-xs w-36 outline-none">
                                    <input type="text" name="url" placeholder="URL do arquivo" required
                                           class="bg-neutral-800 border-none rounded px-3 py-1.5 text-xs w-48 outline-none">
                                    <input type="text" name="quality" placeholder="Qualidade (1080p)"
                                           class="bg-neutral-800 border-none rounded px-3 py-1.5 text-xs w-24 outline-none">
                                    <input type="text" name="size" placeholder="Tamanho (350 MB)"
                                           class="bg-neutral-800 border-none rounded px-3 py-1.5 text-xs w-28 outline-none">

                                    <div class="flex gap-2">
                                        <select name="type" class="bg-neutral-800 rounded text-xs py-1.5 px-2 outline-none border-none">
                                            <option value="direct">DIRETO</option>
                                            <option value="external">EXTERNO</option>
                                        </select>
                                        <select name="download_sub" class="bg-neutral-800 rounded text-xs py-1.5 px-2 outline-none border-none text-yellow-500 font-bold">
                                            <option value="free">FREE</option>
                                            <option value="premium">VIP</option>
                                        </select>
                                    </div>

                                    <button type="submit" class="bg-green-700 text-white px-4 py-1.5 rounded text-xs font-bold hover:bg-green-600 transition shadow-lg">
                                        <i class="fa-solid fa-download mr-1"></i>+ ADD
                                    </button>
                                </form>
                            </div>

                            {{-- Listagem de Downloads do Ep --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @forelse($episode->downloadLinks as $link)
                                    <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-lg relative group border-t-2 {{ $link->download_sub == 'premium' ? 'border-yellow-600' : 'border-green-700' }}">
                                        <form action="{{ route('admin.downloads.series.episode.update', $link->id) }}" method="POST" class="space-y-3">
                                            @csrf @method('PUT')

                                            <div class="flex justify-between items-center">
                                                <input type="text" name="name" value="{{ $link->name }}"
                                                       class="bg-transparent border-none p-0 text-sm font-bold text-white outline-none w-36">
                                                <div class="flex gap-2">
                                                    <button type="submit" title="Salvar" class="text-blue-500 hover:text-white transition">
                                                        <i class="fa-solid fa-floppy-disk"></i>
                                                    </button>
                                                    <button type="button" onclick="deleteDownloadLink({{ $link->id }})" title="Excluir"
                                                            class="text-red-500 hover:text-white transition">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <input type="text" name="url" value="{{ $link->url }}"
                                                   class="w-full bg-neutral-800 border-none rounded px-2 py-1.5 text-xs text-neutral-400 font-mono italic">

                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-[9px] text-neutral-500 uppercase font-bold mb-1">Qualidade</label>
                                                    <input type="text" name="quality" value="{{ $link->quality }}" placeholder="1080p"
                                                           class="w-full bg-neutral-800 border-none rounded px-2 py-1 text-[10px]">
                                                </div>
                                                <div>
                                                    <label class="block text-[9px] text-neutral-500 uppercase font-bold mb-1">Tamanho</label>
                                                    <input type="text" name="size" value="{{ $link->size }}" placeholder="350 MB"
                                                           class="w-full bg-neutral-800 border-none rounded px-2 py-1 text-[10px]">
                                                </div>
                                            </div>

                                            <div class="flex justify-between items-center gap-2 pt-1 border-t border-neutral-800">
                                                <select name="type" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px]">
                                                    <option value="direct" {{ $link->type == 'direct' ? 'selected' : '' }}>DIRETO</option>
                                                    <option value="external" {{ $link->type == 'external' ? 'selected' : '' }}>EXTERNO</option>
                                                </select>
                                                <select name="download_sub" class="bg-neutral-800 border-none rounded px-2 py-1 text-[10px] {{ $link->download_sub == 'premium' ? 'text-yellow-500' : 'text-green-400' }}">
                                                    <option value="free" {{ $link->download_sub == 'free' ? 'selected' : '' }}>FREE</option>
                                                    <option value="premium" {{ $link->download_sub == 'premium' ? 'selected' : '' }}>VIP</option>
                                                </select>
                                                <input type="number" name="order" value="{{ $link->order }}" placeholder="Ord"
                                                       class="bg-neutral-800 border-none rounded px-2 py-1 text-[9px] w-14">
                                            </div>
                                        </form>

                                        <form id="delete-dl-{{ $link->id }}" action="{{ route('admin.downloads.series.episode.delete', $link->id) }}" method="POST" class="hidden">
                                            @csrf @method('DELETE')
                                        </form>
                                    </div>
                                @empty
                                    <div class="col-span-full border border-dashed border-neutral-800 p-4 text-center text-[10px] text-neutral-600 italic">
                                        Nenhum link de download configurado para este episódio.
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

    function deleteDownloadLink(id) {
        if(confirm('Deseja remover este link de download?')) {
            document.getElementById(`delete-dl-${id}`).submit();
        }
    }
</script>
@endsection

@extends('layouts.admin')

@section('title', 'Links do Evento')

@section('content')
<section>
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.events.index') }}" class="w-10 h-10 bg-neutral-900 border border-neutral-800 rounded-full flex items-center justify-center hover:bg-neutral-800 transition text-white">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-white">Gerenciar Links</h2>
                <p class="text-sm text-neutral-500">Evento: <span class="text-netflix font-bold">{{ $event->title }}</span></p>
            </div>
        </div>

        <a href="{{ route('admin.events.links.create', $event->id) }}" class="bg-netflix hover:bg-red-700 text-white font-bold px-6 py-2.5 rounded shadow-lg transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> ADICIONAR LINK
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-900/20 border border-green-600 text-green-400 px-4 py-3 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-900 border border-neutral-800 rounded-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-800/50 text-neutral-400">
                    <tr>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">NOME DO PLAYER</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">TIPO</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">ACESSO</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">URL (ABREVIADA)</th>
                        <th class="p-4 text-right font-bold uppercase tracking-widest text-[10px]">AÇÕES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800">
                    @forelse($links as $link)
                        <tr class="hover:bg-neutral-800/20 transition group">
                            <td class="p-4">
                                <span class="text-white font-bold">{{ $link->name }}</span>
                            </td>
                            <td class="p-4">
                                <span class="bg-neutral-800 text-neutral-400 text-[10px] px-2 py-0.5 rounded border border-neutral-700/50 uppercase font-mono">
                                    {{ $link->type }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if($link->player_sub === 'premium')
                                    <span class="flex items-center gap-1.5 text-yellow-500 font-bold uppercase text-[10px]">
                                        <i class="fa-solid fa-crown"></i> VIP
                                    </span>
                                @else
                                    <span class="flex items-center gap-1.5 text-blue-400 font-bold uppercase text-[10px]">
                                        <i class="fa-solid fa-star"></i> FREE
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="text-neutral-500 font-mono text-[10px] break-all">{{ Str::limit($link->url, 50) }}</span>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-3 opacity-20 group-hover:opacity-100 transition">
                                    <a href="{{ route('admin.events.links.edit', $link->id) }}" class="text-neutral-400 hover:text-white">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.events.links.destroy', $link->id) }}" method="POST" class="inline" onsubmit="return confirm('Remover este link?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-400">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-neutral-500">
                                <i class="fa-solid fa-link-slash text-3xl mb-3 block opacity-20"></i>
                                Nenhum link adicionado para este evento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

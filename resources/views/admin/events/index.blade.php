@extends('layouts.admin')

@section('title', 'Eventos Ao Vivo')

@section('content')
<section>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Eventos Ao Vivo</h2>
            <p class="text-sm text-neutral-500">Gerencie transmissões esportivas e eventos em tempo real.</p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('admin.events.create') }}" class="bg-netflix hover:bg-red-700 text-white font-bold px-6 py-2.5 rounded shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> NOVO EVENTO
            </a>
        </div>
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
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">EVENTO / TIMES</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">HORÁRIO (SP)</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">STATUS</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">LINKS</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">VISIBILIDADE</th>
                        <th class="p-4 text-right font-bold uppercase tracking-widest text-[10px]">AÇÕES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800">
                    @forelse($events as $event)
                        <tr class="hover:bg-neutral-800/20 transition group">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    @if($event->image_url)
                                        <img src="{{ $event->image_url }}" class="w-10 h-10 rounded object-cover border border-neutral-700">
                                    @else
                                        <div class="w-10 h-10 bg-neutral-800 rounded flex items-center justify-center border border-neutral-700">
                                            <i class="fa-solid fa-trophy text-neutral-600"></i>
                                        </div>
                                    @endif
                                    <div class="flex flex-col">
                                        <span class="text-white font-bold">{{ $event->title }}</span>
                                        <span class="text-neutral-500 text-xs">
                                            @if($event->home_team && $event->away_team)
                                                {{ $event->home_team }} x {{ $event->away_team }}
                                            @else
                                                Transmissão Única
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 font-mono text-xs">
                                <div class="flex flex-col text-neutral-400">
                                    <span>Início: {{ $event->start_time->format('d/m H:i') }}</span>
                                    <span>Fim: {{ $event->end_time->format('d/m H:i') }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                @php
                                    $status = $event->status;
                                    $color = match($status) {
                                        'Ao Vivo' => 'text-red-500 border-red-500/30 bg-red-500/10',
                                        'Em Breve' => 'text-yellow-500 border-yellow-500/30 bg-yellow-500/10',
                                        'Encerrado' => 'text-neutral-500 border-neutral-700 bg-neutral-800',
                                        default => 'text-blue-500 border-blue-500/30 bg-blue-500/10'
                                    };
                                @endphp
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded border {{ $color }} uppercase">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="p-4">
                                <a href="{{ route('admin.events.links', $event->id) }}" class="text-netflix hover:underline flex items-center gap-1.5 font-bold text-xs">
                                    <i class="fa-solid fa-link"></i> {{ $event->links->count() }} LINKS
                                </a>
                            </td>
                            <td class="p-4">
                                @if($event->is_active)
                                    <span class="text-green-500 flex items-center gap-1 text-[10px] font-bold">
                                        <i class="fa-solid fa-circle text-[6px]"></i> ATIVO
                                    </span>
                                @else
                                    <span class="text-neutral-600 flex items-center gap-1 text-[10px] font-bold">
                                        <i class="fa-solid fa-circle text-[6px]"></i> INATIVO
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-3 opacity-20 group-hover:opacity-100 transition">
                                    <a href="{{ route('admin.events.edit', $event->id) }}" class="text-neutral-400 hover:text-white">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.events.destroy', $event->id) }}" method="POST" class="inline" onsubmit="return confirm('Excluir evento?')">
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
                            <td colspan="6" class="p-10 text-center text-neutral-500">
                                <i class="fa-solid fa-calendar-xmark text-3xl mb-3 block opacity-20"></i>
                                Nenhum evento cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">
        {{ $events->links() }}
    </div>
</section>
@endsection

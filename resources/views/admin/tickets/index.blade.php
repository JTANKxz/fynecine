@extends('layouts.admin')

@section('title', 'Suporte e Tickets')

@section('content')
<section>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold">Central de Suporte</h2>
            <p class="text-xs text-neutral-500">Gerencie solicitações e problemas reportados pelos usuários.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.tickets.index') }}" class="px-3 py-1.5 rounded text-xs font-bold {{ !request('status') ? 'bg-netflix text-white' : 'bg-neutral-800 text-neutral-400 hover:bg-neutral-700' }}">TODOS</a>
            <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" class="px-3 py-1.5 rounded text-xs font-bold {{ request('status') == 'open' ? 'bg-netflix text-white' : 'bg-neutral-800 text-neutral-400 hover:bg-neutral-700' }}">ABERTOS</a>
            <a href="{{ route('admin.tickets.index', ['status' => 'in_progress']) }}" class="px-3 py-1.5 rounded text-xs font-bold {{ request('status') == 'in_progress' ? 'bg-blue-600 text-white' : 'bg-neutral-800 text-neutral-400 hover:bg-neutral-700' }}">EM ANDAMENTO</a>
            <a href="{{ route('admin.tickets.index', ['status' => 'closed']) }}" class="px-3 py-1.5 rounded text-xs font-bold {{ request('status') == 'closed' ? 'bg-green-600 text-white' : 'bg-neutral-800 text-neutral-400 hover:bg-neutral-700' }}">FECHADOS</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-900/30 border border-green-600 text-green-400 px-4 py-3 rounded relative text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-4">
        @forelse ($tickets as $ticket)
            <div class="bg-neutral-900 border border-neutral-800 rounded-lg p-5 hover:border-neutral-700 transition group">
                <div class="flex flex-col md:flex-row justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $ticket->status == 'open' ? 'bg-red-600/20 text-red-500 border border-red-600/20' : '' }}
                                {{ $ticket->status == 'in_progress' ? 'bg-blue-600/20 text-blue-500 border border-blue-600/20' : '' }}
                                {{ $ticket->status == 'closed' ? 'bg-green-600/20 text-green-500 border border-green-600/20' : '' }}
                            ">
                                {{ $ticket->status == 'open' ? 'Aberto' : ($ticket->status == 'in_progress' ? 'Em Andamento' : 'Fechado') }}
                            </span>
                            <span class="text-xs text-neutral-500 font-medium">#{{ $ticket->id }} • {{ $ticket->created_at->diffForHumans() }}</span>
                        </div>
                        
                        <h3 class="text-white font-bold text-lg mb-1 flex items-center gap-2">
                            <span class="text-netflix">[{{ strtoupper($ticket->topic) }}]</span>
                            {{ $ticket->subtopic }}
                        </h3>
                        
                        <p class="text-neutral-400 text-sm leading-relaxed mb-4">
                            {{ $ticket->message }}
                        </p>

                        <div class="flex items-center gap-3">
                            <img src="{{ $ticket->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($ticket->user->name) }}" class="w-6 h-6 rounded-full border border-neutral-800">
                            <span class="text-xs text-neutral-500">Enviado por <strong class="text-neutral-300">{{ $ticket->user->name }}</strong> ({{ $ticket->user->email }})</span>
                        </div>
                    </div>

                    <div class="flex md:flex-col justify-end gap-2 border-t md:border-t-0 md:border-l border-neutral-800 pt-4 md:pt-0 md:pl-6 shrink-0">
                        <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            @if($ticket->status == 'open')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-4 rounded transition">
                                    ATENDER
                                </button>
                            @elseif($ticket->status == 'in_progress')
                                <input type="hidden" name="status" value="closed">
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-2 px-4 rounded transition">
                                    FECHAR
                                </button>
                            @else
                                <input type="hidden" name="status" value="open">
                                <button type="submit" class="w-full bg-neutral-800 hover:bg-neutral-700 text-neutral-400 text-xs font-bold py-2 px-4 rounded transition">
                                    REABRIR
                                </button>
                            @endif
                        </form>

                        <form action="{{ route('admin.tickets.delete', $ticket->id) }}" method="POST" class="inline" onsubmit="return confirm('Excluir ticket?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full bg-red-900/20 hover:bg-red-900/40 text-red-500 text-xs font-bold py-2 px-4 rounded border border-red-900/30 transition">
                                EXCLUIR
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-neutral-900 rounded-lg p-10 text-center border border-neutral-800">
                <i class="fa-solid fa-headset text-4xl text-neutral-700 mb-4"></i>
                <p class="text-neutral-500">Nenhum ticket encontrado.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $tickets->links() }}
    </div>
</section>
@endsection

@extends('layouts.admin')

@section('title', 'Notificações In-App')

@section('content')
<section>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Gestão de Notificações</h2>
            <p class="text-sm text-neutral-500">Controle disparos Push e histórico interno (In-App).</p>
        </div>

        <a href="{{ route('admin.notifications.create') }}" class="bg-netflix hover:bg-red-700 text-white font-bold px-6 py-2.5 rounded shadow-lg transition flex items-center gap-2">
            <i class="fa-solid fa-paper-plane"></i> NOVO DISPARO
        </a>
    </div>

    {{-- Abas de Navegação --}}
    <div class="flex items-center gap-2 mb-6 border-b border-neutral-800 pb-px overflow-x-auto whitespace-nowrap">
        <a href="{{ route('admin.notifications.index') }}" 
           class="px-6 py-3 text-sm font-bold transition border-b-2 {{ !request('type') ? 'border-purple-600 text-white' : 'border-transparent text-neutral-500 hover:text-white' }}">
            Todas as Notificações
        </a>
        <a href="{{ route('admin.notifications.index', ['type' => 'push']) }}" 
           class="px-6 py-3 text-sm font-bold transition border-b-2 {{ request('type') === 'push' ? 'border-purple-600 text-white' : 'border-transparent text-neutral-500 hover:text-white' }}">
            <i class="fa-solid fa-mobile-screen-button mr-2"></i> Disparos Push
        </a>
        <a href="{{ route('admin.notifications.index', ['type' => 'in_app']) }}" 
           class="px-6 py-3 text-sm font-bold transition border-b-2 {{ request('type') === 'in_app' ? 'border-purple-600 text-white' : 'border-transparent text-neutral-500 hover:text-white' }}">
            <i class="fa-solid fa-bell mr-2"></i> Histórico In-App
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-900/20 border border-green-600 text-green-400 px-4 py-3 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-900/20 border border-red-600 text-red-400 px-4 py-3 rounded text-sm">
            <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="bg-neutral-900 border border-neutral-800 rounded-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-800/50 text-neutral-400">
                    <tr>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">TÍTULO / MODO</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">ALVO</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">AÇÃO</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">DATA ENVIO</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">EXPIRAÇÃO</th>
                        <th class="p-4 text-right font-bold uppercase tracking-widest text-[10px]">AÇÕES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800">
                    @forelse($notifications as $n)
                        <tr class="hover:bg-neutral-800/20 transition group">
                            <td class="p-4">
                                <div class="flex flex-col">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-white font-bold">{{ $n->title }}</span>
                                        <div class="flex gap-1">
                                            @if($n->push_status && $n->push_status !== 'none')
                                                <span class="bg-blue-600/20 text-blue-400 text-[8px] font-black px-1.5 py-0.5 rounded border border-blue-600/30" title="Push Enviado">
                                                    PUSH
                                                </span>
                                            @endif
                                            @if($n->is_in_app)
                                                <span class="bg-green-600/20 text-green-400 text-[8px] font-black px-1.5 py-0.5 rounded border border-green-600/30" title="Salvo no Histórico">
                                                    IN-APP
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-neutral-500 text-xs line-clamp-1 italic">{{ $n->content }}</span>
                                </div>
                            </td>
                            <td class="p-4">
                                @if($n->segment === 'all')
                                    <span class="bg-blue-900/20 text-blue-400 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-900/30">
                                        <i class="fa-solid fa-earth-americas mr-1"></i> GLOBAL
                                    </span>
                                @elseif($n->segment === 'individual')
                                    <span class="bg-purple-900/20 text-purple-400 text-[10px] font-bold px-2 py-0.5 rounded border border-purple-900/30">
                                        <i class="fa-solid fa-user mr-1"></i> USER ID {{ $n->user_id }}
                                    </span>
                                @elseif(in_array($n->segment, ['free', 'basic', 'premium']))
                                    <span class="bg-yellow-900/20 text-yellow-400 text-[10px] font-bold px-2 py-0.5 rounded border border-yellow-900/30">
                                        <i class="fa-solid fa-tag mr-1"></i> PLANO: {{ strtoupper($n->segment) }}
                                    </span>
                                @elseif($n->segment === 'guest')
                                    <span class="bg-orange-900/20 text-orange-400 text-[10px] font-bold px-2 py-0.5 rounded border border-orange-900/30">
                                        <i class="fa-solid fa-user-slash mr-1"></i> GUESTS
                                    </span>
                                @else
                                    <span class="bg-neutral-900/20 text-neutral-400 text-[10px] font-bold px-2 py-0.5 rounded border border-neutral-900/30">
                                        {{ strtoupper($n->segment) }}
                                    </span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <span class="bg-neutral-800 text-neutral-400 text-[10px] px-2 py-0.5 rounded border border-neutral-700/50 uppercase">
                                        {{ $n->action_type }}
                                    </span>
                                    @if($n->action_value)
                                        <span class="text-[10px] text-neutral-500 font-mono">{{ Str::limit($n->action_value, 20) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4 text-neutral-400 font-mono text-xs">
                                {{ $n->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-4">
                                @if($n->expires_at)
                                    @if($n->expires_at->isPast())
                                        <span class="text-red-500 text-xs font-bold uppercase opacity-50">EXPIRADA</span>
                                    @else
                                        <span class="text-neutral-400 text-xs font-mono">{{ $n->expires_at->format('d/m/Y') }}</span>
                                    @endif
                                @else
                                    <span class="text-neutral-600 text-[10px]">Nunca</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <form action="{{ route('admin.notifications.destroy', $n) }}" method="POST" class="inline" onsubmit="return confirm('Deletar notificação permanentemente?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-400 transition opacity-20 group-hover:opacity-100">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-neutral-500">
                                <i class="fa-solid fa-bell-slash text-3xl mb-3 block opacity-20"></i>
                                Nenhuma notificação enviada ainda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">
        {{ $notifications->links() }}
    </div>
</section>
@endsection

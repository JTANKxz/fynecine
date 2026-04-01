@extends('layouts.admin')

@section('title', 'Notificações In-App')

@section('content')
<section>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white uppercase tracking-tighter">Histórico In-App</h2>
            <p class="text-sm text-neutral-500 font-medium">Controle as mensagens internas que aparecem no "Sininho" dos usuários.</p>
        </div>

        <a href="{{ route('admin.in-app-notifications.create') }}" class="bg-netflix hover:opacity-90 text-white font-black px-8 py-3 rounded-xl shadow-2xl transition flex items-center gap-3 active:scale-95">
            <i class="fa-solid fa-plus-circle"></i> NOVA MENSAGEM
        </a>
    </div>

    {{-- Filtros --}}
    <div class="mb-8 p-4 bg-neutral-900/50 border border-neutral-800 rounded-2xl flex flex-wrap gap-4 items-end">
        <form action="{{ route('admin.in-app-notifications.index') }}" method="GET" class="flex flex-wrap gap-4 w-full">
            <div class="flex-1 min-w-[150px]">
                <label class="block text-[10px] font-black text-neutral-600 uppercase tracking-widest mb-2 pl-1">Público</label>
                <select name="segment" class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-purple-600 transition">
                    <option value="">Todos os Públicos</option>
                    <option value="all" {{ request('segment') == 'all' ? 'selected' : '' }}>Global</option>
                    <option value="premium" {{ request('segment') == 'premium' ? 'selected' : '' }}>VIP</option>
                    <option value="free" {{ request('segment') == 'free' ? 'selected' : '' }}>Free</option>
                    <option value="individual" {{ request('segment') == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="guest" {{ request('segment') == 'guest' ? 'selected' : '' }}>Visitantes</option>
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="block text-[10px] font-black text-neutral-600 uppercase tracking-widest mb-2 pl-1">Ação</label>
                <select name="action" class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-purple-600 transition">
                    <option value="">Todas as Ações</option>
                    <option value="none" {{ request('action') == 'none' ? 'selected' : '' }}>Nenhuma</option>
                    <option value="movie" {{ request('action') == 'movie' ? 'selected' : '' }}>Filme</option>
                    <option value="series" {{ request('action') == 'series' ? 'selected' : '' }}>Série</option>
                    <option value="url" {{ request('action') == 'url' ? 'selected' : '' }}>Link</option>
                    <option value="plans" {{ request('action') == 'plans' ? 'selected' : '' }}>Planos</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-6 py-2 rounded-xl transition text-xs whitespace-nowrap">
                    <i class="fa-solid fa-filter mr-1"></i> FILTRAR
                </button>
                @if(request()->filled('segment') || request()->filled('action'))
                    <a href="{{ route('admin.in-app-notifications.index') }}" class="text-neutral-500 hover:text-white transition text-xs font-bold px-2 whitespace-nowrap">
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 px-5 py-4 rounded-xl text-sm flex items-center gap-3">
            <i class="fa-solid fa-circle-check"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-950 border border-neutral-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-900 text-neutral-500">
                    <tr>
                        <th class="p-5 font-black uppercase tracking-widest text-[10px]">INFORMAÇÃO</th>
                        <th class="p-5 font-black uppercase tracking-widest text-[10px]">PÚBLICO</th>
                        <th class="p-5 font-black uppercase tracking-widest text-[10px]">INTERAÇÃO</th>
                        <th class="p-5 font-black uppercase tracking-widest text-[10px]">DATA</th>
                        <th class="p-5 text-right font-black uppercase tracking-widest text-[10px]">AÇÕES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-900">
                    @forelse($notifications as $n)
                        <tr class="hover:bg-neutral-900/40 transition group">
                            <td class="p-5">
                                <div class="flex items-center gap-4">
                                    @if($n->image_url)
                                        <img src="{{ $n->image_url }}" class="w-10 h-10 object-cover rounded-lg border border-neutral-800 shadow">
                                    @else
                                        <div class="w-10 h-10 bg-neutral-900 rounded-lg flex items-center justify-center border border-neutral-800">
                                            <i class="fa-solid fa-bell text-neutral-600"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-white font-bold mb-0.5">{{ $n->title }}</div>
                                        <div class="text-neutral-500 text-xs line-clamp-1 italic max-w-[250px]">{{ $n->content }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-5">
                                @if($n->is_global)
                                    <span class="bg-blue-500/10 text-blue-400 text-[10px] font-black px-2.5 py-1 rounded-md border border-blue-500/20 uppercase">
                                        <i class="fa-solid fa-globe mr-1.5"></i> Global
                                    </span>
                                @else
                                    <span class="bg-yellow-500/10 text-yellow-500/80 text-[10px] font-black px-2.5 py-1 rounded-md border border-neutral-800 uppercase tracking-tighter">
                                        <i class="fa-solid fa-user mr-1.5"></i> ID: {{ $n->user_id }}
                                    </span>
                                @endif
                                <div class="mt-1.5 text-[9px] text-neutral-600 font-bold uppercase tracking-widest">
                                    Segmento: {{ $n->segment }}
                                </div>
                            </td>
                            <td class="p-5">
                                <div class="flex items-center gap-2">
                                    <span class="bg-neutral-900 text-neutral-400 text-[9px] font-bold px-2 py-0.5 rounded border border-neutral-800 uppercase">
                                        {{ $n->action_type }}
                                    </span>
                                    @if($n->action_value)
                                        <span class="text-[10px] text-neutral-500 font-mono">{{ Str::limit($n->action_value, 15) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-5 text-neutral-400 font-mono text-[11px]">
                                {{ $n->created_at->format('d/m/Y') }}<br>
                                <span class="text-[9px] text-neutral-600 font-bold uppercase">{{ $n->created_at->format('H:i') }}</span>
                            </td>
                            <td class="p-5 text-right">
                                <div class="flex justify-end gap-3 translate-x-2 opacity-10 group-hover:opacity-100 transition duration-300">
                                    <form action="{{ route('admin.in-app-notifications.destroy', $n) }}" method="POST" onsubmit="return confirm('Excluir do histórico definitivamente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-9 h-9 bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white rounded-lg transition flex items-center justify-center">
                                            <i class="fa-solid fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-16 h-16 bg-neutral-900 rounded-full flex items-center justify-center border border-neutral-800 text-neutral-700">
                                        <i class="fa-solid fa-bell-slash text-2xl"></i>
                                    </div>
                                    <p class="text-neutral-500 font-medium">Nenhuma mensagem no histórico In-App.</p>
                                </div>
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

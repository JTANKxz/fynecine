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
                        <button type="button" 
                                onclick="openResponseModal('{{ route('admin.tickets.respond', $ticket->id) }}', '{{ $ticket->user->name }}', 'Ticket #{{ $ticket->id }}')"
                                class="w-full bg-netflix hover:bg-red-700 text-white text-xs font-bold py-2 px-4 rounded transition mb-1">
                            RESPONDER + NOTIFICAR
                        </button>

                        <form action="{{ route('admin.tickets.update', $ticket->id) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            @if($ticket->status == 'open')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 px-4 rounded transition">
                                    ATENDER
                                </button>
                            @elseif($ticket->status == 'in_progress' || $ticket->status == 'answered')
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

<!-- MODAL DE RESPOSTA -->
<div id="responseModal" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-neutral-900 border border-neutral-800 rounded-xl w-full max-w-lg overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-neutral-800 flex justify-between items-center bg-neutral-800/50">
            <h3 class="font-bold text-lg text-white">Responder: <span id="modalTargetName" class="text-netflix"></span></h3>
            <button onclick="closeResponseModal()" class="text-neutral-400 hover:text-white text-xl">&times;</button>
        </div>
        
        <form id="responseForm" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Mensagem de Resposta</label>
                <textarea name="message" required rows="4" 
                    class="w-full bg-black border border-neutral-800 rounded-lg p-3 text-sm focus:border-netflix outline-none text-white placeholder-neutral-700"
                    placeholder="Sua resposta personalizada aqui..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Novo Status</label>
                    <select name="status" class="w-full bg-black border border-neutral-800 rounded-lg p-2.5 text-sm outline-none text-white focus:border-netflix">
                        <option value="answered" selected>Respondido</option>
                        <option value="closed">Fechado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Notificar por:</label>
                    <div class="flex items-center gap-4 mt-2">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="send_in_app" value="1" checked class="rounded border-neutral-800 bg-black text-netflix focus:ring-netflix">
                            <span class="text-xs text-neutral-300 group-hover:text-white transition">In-App</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="send_push" value="1" class="rounded border-neutral-800 bg-black text-netflix focus:ring-netflix">
                            <span class="text-xs text-neutral-300 group-hover:text-white transition">Push</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="border-t border-neutral-800 pt-4">
                <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Vincular Conteúdo (Deep Link)</label>
                <div class="relative">
                    <div class="flex gap-2 mb-2">
                        <select id="contentSearchType" class="bg-neutral-800 border-none rounded text-xs px-2 text-white outline-none">
                            <option value="movie">Filme</option>
                            <option value="series">Série</option>
                        </select>
                        <input type="text" id="contentSearchInput" placeholder="Buscar título..." 
                            class="flex-1 bg-black border border-neutral-800 rounded-lg px-3 py-2 text-xs outline-none focus:border-netflix text-white">
                    </div>
                    
                    <div id="searchResults" class="hidden absolute top-full left-0 right-0 bg-neutral-800 rounded-lg shadow-xl border border-neutral-700 mt-1 max-h-40 overflow-y-auto z-10">
                        <!-- Resultados aqui -->
                    </div>

                    <div id="selectedContent" class="hidden flex items-center justify-between bg-netflix/10 border border-netflix/30 rounded-lg p-2 mt-2">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-link text-netflix text-xs"></i>
                            <span id="selectedContentTitle" class="text-xs text-white"></span>
                        </div>
                        <button type="button" onclick="clearSelectedContent()" class="text-neutral-500 hover:text-white text-sm">&times;</button>
                    </div>

                    <input type="hidden" name="action_type" id="hiddenActionType" value="none">
                    <input type="hidden" name="action_value" id="hiddenActionValue" value="">
                </div>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full bg-netflix hover:bg-red-700 text-white font-bold py-3 rounded-lg transition shadow-lg flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    ENVIAR RESPOSTA E NOTIFICAR
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openResponseModal(action, name, id) {
        document.getElementById('responseForm').action = action;
        document.getElementById('modalTargetName').innerText = name + ' (' + id + ')';
        document.getElementById('responseModal').classList.remove('hidden');
    }

    function closeResponseModal() {
        document.getElementById('responseModal').classList.add('hidden');
        clearSelectedContent();
    }

    // Search logic
    let searchTimeout = null;
    const searchInput = document.getElementById('contentSearchInput');
    const searchResults = document.getElementById('searchResults');
    const searchType = document.getElementById('contentSearchType');

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const q = this.value;
        const type = searchType.value;

        if (q.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`/dashzin/notifications/search-content?q=${q}&type=${type}`)
                .then(res => res.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'p-2 hover:bg-neutral-700 cursor-pointer border-b border-neutral-700 last:border-0 flex items-center gap-2';
                            div.innerHTML = `
                                <img src="${item.poster || 'https://via.placeholder.com/30?text=?'}" class="w-6 h-8 rounded object-cover">
                                <span class="text-[10px] text-white truncate">${item.title}</span>
                            `;
                            div.onclick = () => selectContent(item, type);
                            searchResults.appendChild(div);
                        });
                        searchResults.classList.remove('hidden');
                    } else {
                        searchResults.classList.add('hidden');
                    }
                });
        }, 300);
    });

    function selectContent(item, type) {
        document.getElementById('hiddenActionType').value = (type === 'movie' ? 'movie' : 'series');
        document.getElementById('hiddenActionValue').value = item.id;
        document.getElementById('selectedContentTitle').innerText = item.title;
        document.getElementById('selectedContent').classList.remove('hidden');
        searchResults.classList.add('hidden');
        searchInput.value = '';
    }

    function clearSelectedContent() {
        document.getElementById('hiddenActionType').value = 'none';
        document.getElementById('hiddenActionValue').value = '';
        document.getElementById('selectedContent').classList.add('hidden');
    }

    // Close on overlay click
    document.getElementById('responseModal').addEventListener('click', function(e) {
        if (e.target === this) closeResponseModal();
    });
</script>
@endpush

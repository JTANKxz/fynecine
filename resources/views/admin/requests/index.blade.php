@extends('layouts.admin')

@section('title', 'Pedidos de Conteúdo')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-white">Pedidos da Comunidade</h1>
            <p class="mt-2 text-sm text-neutral-400">Usuários solicitam filmes e séries pelo App. Importe diretamente daqui.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mt-4 bg-green-900 border border-green-600 text-green-100 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mt-4 bg-red-900 border border-red-600 text-red-100 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-neutral-800">
                        <thead class="bg-neutral-800">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-6">Usuário</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">TMDB / Título</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Data Pedido</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800 bg-neutral-900">
                            @forelse ($requests as $req)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <img class="h-10 w-10 rounded-full" src="{{ $req->user->avatar ?? 'https://ui-avatars.com/api/?name='.$req->user->name }}" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-medium text-white">{{ collect(explode(' ', $req->user->name))->take(2)->implode(' ') }} <span class="bg-neutral-800 text-xs px-2 py-0.5 rounded ml-2">{{ strtoupper($req->user->plan_type) }}</span></div>
                                                <div class="text-neutral-500">{{ $req->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-neutral-300">
                                        <div class="text-white font-semibold flex items-center gap-2">
                                            @if($req->type === 'movie')
                                            <span class="bg-blue-900 text-blue-200 text-xs px-1.5 py-0.5 rounded">FILME</span>
                                            @else
                                            <span class="bg-purple-900 text-purple-200 text-xs px-1.5 py-0.5 rounded">TV</span>
                                            @endif
                                            {{ $req->title }} ({{ $req->year ?? 'Ano N/D' }})
                                        </div>
                                        <div class="text-xs text-neutral-500 mt-1">ID TMDB: {{ $req->tmdb_id }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-neutral-400">
                                        {{ $req->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-neutral-300">
                                        @if($req->status === 'approved')
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Aprovado</span>
                                        @elseif($req->status === 'rejected')
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Rejeitado</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Pendente</span>
                                        @endif
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 flex justify-end gap-2 items-center">
                                        
                                        @if($req->status === 'pending')
                                        {{-- Botão Respond --}}
                                        <button type="button" 
                                                onclick="openResponseModal('{{ route('admin.requests.respond', $req->id) }}', '{{ $req->user->name }}', '{{ $req->title }}')"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded mr-2 text-xs font-bold transition">
                                            RESPONDER + NOTIFICAR
                                        </button>

                                        {{-- Botão mágico Importar --}}
                                        <form action="{{ route('admin.requests.autoimport', $req) }}" method="POST" class="inline" onsubmit="return confirm('Isso fará o download de todos os episódios/links originais do TMDB. Continuar?')">
                                            @csrf
                                            <button type="submit" class="bg-netflix hover:bg-red-700 text-white px-3 py-1.5 rounded mr-2 text-xs font-bold transition">
                                                <i class="fa-solid fa-download mr-1"></i> Auto-Importar
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.requests.update', $req) }}" method="POST" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="text-neutral-400 hover:text-white mr-3 text-xs">
                                                Rejeitar
                                            </button>
                                        </form>
                                        @endif

                                        <form action="{{ route('admin.requests.destroy', $req) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja apagar o registro?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-400 ml-1 text-xs">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-neutral-400">Nenhum pedido de filme/série pendente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DE RESPOSTA -->
<div id="responseModal" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-neutral-900 border border-neutral-800 rounded-xl w-full max-w-lg overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-neutral-800 flex justify-between items-center bg-neutral-800/50">
            <h3 class="font-bold text-lg text-white">Responder Pedido: <span id="modalTargetName" class="text-netflix"></span></h3>
            <button onclick="closeResponseModal()" class="text-neutral-400 hover:text-white text-xl">&times;</button>
        </div>
        
        <form id="responseForm" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Mensagem de Resposta</label>
                <textarea name="message" required rows="4" 
                    class="w-full bg-black border border-neutral-800 rounded-lg p-3 text-sm focus:border-netflix outline-none text-white placeholder-neutral-700"
                    placeholder="Olá! Seu pedido foi processado..."></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Status do Pedido</label>
                    <select name="status" class="w-full bg-black border border-neutral-800 rounded-lg p-2.5 text-sm outline-none text-white focus:border-netflix">
                        <option value="approved" selected>Aprovado</option>
                        <option value="rejected">Rejeitado</option>
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
                <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Vincular Conteúdo Adicionado (Opcional)</label>
                <div class="relative">
                    <div class="flex gap-2 mb-2">
                        <select id="contentSearchType" class="bg-neutral-800 border-none rounded text-xs px-2 text-white outline-none">
                            <option value="movie">Filme</option>
                            <option value="series">Série</option>
                        </select>
                        <input type="text" id="contentSearchInput" placeholder="Buscar título no catálogo..." 
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
                    <i class="fa-solid fa-check-circle"></i>
                    APROVAR E NOTIFICAR USUÁRIO
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openResponseModal(action, userName, requestTitle) {
        document.getElementById('responseForm').action = action;
        document.getElementById('modalTargetName').innerText = userName + ' (' + requestTitle + ')';
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

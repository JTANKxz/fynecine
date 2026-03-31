@extends('layouts.admin')

@section('title', 'Nova Notificação')

@section('content')
<section class="max-w-4xl">
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('admin.notifications.index') }}" class="w-10 h-10 bg-neutral-900 border border-neutral-800 rounded-full flex items-center justify-center hover:bg-neutral-800 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-white">Disparar Notificação FCM</h2>
            <p class="text-sm text-neutral-500">Crie alertas profissionais com Big Picture e segmentação.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-900/20 border border-red-600 text-red-400 px-4 py-3 rounded text-sm">
            <ul class="list-disc ml-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-6 md:p-8 shadow-2xl">
        <form action="{{ route('admin.notifications.store') }}" method="POST" class="space-y-6">
            @csrf

            {{-- Delivery Modes (Two Modes Selection) --}}
            <div class="bg-black/60 border border-purple-900/30 p-5 rounded-2xl grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                <div class="flex items-center gap-4 bg-neutral-900/50 border border-neutral-800 p-4 rounded-xl hover:border-purple-500/50 transition cursor-pointer group">
                    <input type="checkbox" name="send_push" id="send_push" value="1" checked
                        class="w-6 h-6 accent-purple-600 cursor-pointer">
                    <div class="flex-1">
                        <label for="send_push" class="block text-sm font-black text-white uppercase tracking-wider cursor-pointer group-hover:text-purple-400 transition">Modo 1: Push Notification</label>
                        <p class="text-[10px] text-neutral-500">Disparo em tempo real para celulares (FCM/OneSignal).</p>
                    </div>
                    <i class="fa-solid fa-mobile-screen-button text-neutral-700 group-hover:text-purple-500 transition"></i>
                </div>
                
                <div class="flex items-center gap-4 bg-neutral-900/50 border border-neutral-800 p-4 rounded-xl hover:border-green-500/50 transition cursor-pointer group">
                    <input type="checkbox" name="is_in_app" id="is_in_app" value="1" checked
                        class="w-6 h-6 accent-green-600 cursor-pointer">
                    <div class="flex-1">
                        <label for="is_in_app" class="block text-sm font-black text-white uppercase tracking-wider cursor-pointer group-hover:text-green-400 transition">Modo 2: Histórico In-App</label>
                        <p class="text-[10px] text-neutral-500">Salva no histórico interno do app (Sininho).</p>
                    </div>
                    <i class="fa-solid fa-bell text-neutral-700 group-hover:text-green-500 transition"></i>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Título --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Título do Alerta</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Ex: Novo Filme Disponível!"
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition">
                </div>

                {{-- Image URL (Icon/Small) --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">URL do Ícone (Opcional)</label>
                    <input type="url" name="image_url" value="{{ old('image_url') }}" placeholder="https://..."
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition text-xs font-mono">
                </div>
            </div>

            {{-- Big Picture URL --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">URL da Imagem Grande (Big Picture - 16:9)</label>
                <input type="url" name="big_picture_url" value="{{ old('big_picture_url') }}" placeholder="https://... (Sugerido 1280x720)"
                    class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition text-xs font-mono">
                <p class="text-[9px] text-neutral-500">Aparecerá expandida na central de notificações do Android.</p>
            </div>

            {{-- Conteúdo --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Mensagem (Corpo da Notificação)</label>
                <textarea name="content" rows="3" required placeholder="Escreva aqui o corpo da notificação..."
                    class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition">{{ old('content') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Segmentação --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Público Alvo (Segmento)</label>
                    <select name="segment" id="segment_select" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition cursor-pointer">
                        <option value="all" selected>Todos os Dispositivos</option>
                        <option value="premium">Apenas Usuários Premium</option>
                        <option value="basic">Apenas Usuários Basic</option>
                        <option value="free">Apenas Usuários Free</option>
                        <option value="guest">Apenas Visitantes (Não Logados)</option>
                        <option value="individual">Usuário Individual (ID Específico)</option>
                    </select>
                </div>

                {{-- User ID (Hidden by default) --}}
                <div id="user_id_group" class="space-y-2 hidden">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">ID do Usuário</label>
                    <input type="number" name="user_id" value="{{ old('user_id') }}" placeholder="ID do Usuário"
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Tipo de Ação --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Ação ao Clicar (Deep Link)</label>
                    <select name="action_type" id="action_type" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition cursor-pointer">
                        <option value="none">Nenhuma Ação</option>
                        <option value="url">Abrir Link Externo</option>
                        <option value="movie">Abrir Filme</option>
                        <option value="series">Abrir Série</option>
                        <option value="plans">Abrir Planos</option>
                    </select>
                </div>

                {{-- Valor da Ação --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Valor da Ação / Pesquisa</label>
                    <div class="relative">
                        <input type="text" name="action_value" id="action_value" value="{{ old('action_value') }}" placeholder="Ex: link ou slug"
                            class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-purple-500 transition font-mono text-xs">
                        
                        {{-- Dropdown de Busca AJAX --}}
                        <div id="search_results" class="absolute z-50 w-full mt-1 bg-neutral-900 border border-neutral-800 rounded-xl overflow-hidden hidden shadow-2xl">
                        </div>
                    </div>
                    <p class="text-[9px] text-neutral-500">Digite para pesquisar filmes/séries quando selecionado.</p>
                </div>
            </div>


            <div class="pt-2 flex flex-col md:flex-row gap-8 items-center justify-end">
                <button type="submit" class="w-full md:w-auto bg-purple-600 hover:bg-purple-700 text-white font-black px-12 py-4 rounded-xl shadow-xl transition transform active:scale-95 flex items-center justify-center gap-3">
                    <i class="fa-solid fa-paper-plane"></i> ENVIAR NOTIFICAÇÃO
                </button>
            </div>
        </form>
    </div>
</section>

<script>
    const segmentSelect = document.getElementById('segment_select');
    const userGroup = document.getElementById('user_id_group');
    const actionType = document.getElementById('action_type');
    const actionValue = document.getElementById('action_value');
    const searchResults = document.getElementById('search_results');

    // Toggle User ID field
    segmentSelect.addEventListener('change', () => {
        if (segmentSelect.value === 'individual') {
            userGroup.classList.remove('hidden');
        } else {
            userGroup.classList.add('hidden');
        }
    });

    // Content Search AJAX
    let searchTimeout;
    actionValue.addEventListener('input', () => {
        const query = actionValue.value;
        const type = actionType.value;

        if (type !== 'movie' && type !== 'series') {
            searchResults.classList.add('hidden');
            return;
        }

        clearTimeout(searchTimeout);
        if (query.length < 2) {
            searchResults.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('admin.notifications.search') }}?q=${query}&type=${type}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length > 0) {
                        searchResults.innerHTML = '';
                        data.forEach(item => {
                            const div = document.createElement('div');
                            div.className = 'p-3 flex items-center gap-3 hover:bg-neutral-800 cursor-pointer transition border-b border-neutral-800/50';
                            div.innerHTML = `
                                <img src="${item.poster || 'https://via.placeholder.com/40x60'}" class="w-10 h-14 object-cover rounded shadow">
                                <div class="flex-1">
                                    <div class="text-white text-sm font-bold">${item.title}</div>
                                    <div class="text-neutral-500 text-[10px]">ID: ${item.id}</div>
                                </div>
                            `;
                            div.onclick = () => {
                                // O app geralmente usa ID ou Slug, vamos usar o ID por segurança ou slug se planejado
                                // Aqui vou usar o ID, mas se o app esperar slug, pode ser item.slug
                                actionValue.value = item.id;
                                searchResults.classList.add('hidden');
                            };
                            searchResults.appendChild(div);
                        });
                        searchResults.classList.remove('hidden');
                    } else {
                        searchResults.classList.add('hidden');
                    }
                });
        }, 300);
    });

    // Close search on click outside
    document.addEventListener('click', (e) => {
        if (!searchResults.contains(e.target) && e.target !== actionValue) {
            searchResults.classList.add('hidden');
        }
    });
</script>
@endsection

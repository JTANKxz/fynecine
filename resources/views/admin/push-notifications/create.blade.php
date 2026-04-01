@extends('layouts.admin')

@section('title', 'Novo Disparo Push')

@section('content')
<section class="max-w-4xl mx-auto">
    <div class="mb-10 flex items-center gap-4">
        <a href="{{ route('admin.push-notifications.index') }}" class="w-12 h-12 bg-neutral-900 border border-neutral-800 rounded-2xl flex items-center justify-center hover:bg-neutral-800 transition active:scale-95 text-white">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-3xl font-black text-white uppercase tracking-tighter">Realizar Disparo Push FCM</h2>
            <p class="text-sm text-neutral-500 font-medium italic">As mensagens chegarão instantaneamente nos celulares e TVs dos usuários.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-8 bg-red-500/10 border border-red-500/20 text-red-500 px-6 py-5 rounded-2xl text-sm font-semibold flex items-start gap-4 shadow-2xl">
            <i class="fa-solid fa-bolt mt-0.5 animate-pulse"></i>
            <ul class="list-disc ml-4 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-neutral-950 border border-blue-900/10 rounded-3xl p-8 md:p-10 shadow-2xl relative overflow-hidden group">
        {{-- Subtle background decoration --}}
        <i class="fa-solid fa-bolt absolute -top-10 -right-10 text-blue-900/10 text-[12rem] group-hover:scale-110 transition-transform duration-700 pointer-events-none"></i>

        <form action="{{ route('admin.push-notifications.store') }}" method="POST" class="space-y-8 relative z-10">
            @csrf

            {{-- Modo Híbrido Option --}}
            <div class="bg-blue-950/20 border border-blue-500/10 p-5 rounded-2xl flex items-center gap-4 hover:border-blue-500/30 transition cursor-pointer">
                <input type="checkbox" name="is_in_app" id="is_in_app" value="1" checked
                    class="w-6 h-6 accent-blue-600 cursor-pointer">
                <div class="flex-1">
                    <label for="is_in_app" class="block text-sm font-black text-white uppercase tracking-wider cursor-pointer hover:text-blue-400 transition">Salvar no Histórico (Sininho) também?</label>
                    <p class="text-[10px] text-neutral-500 font-bold uppercase tracking-tighter">Recomendado para que o usuário veja a mensagem depois se não clicar no push.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Título --}}
                <div class="space-y-3">
                    <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest pl-1">Título do Disparo</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Ex: Hoje tem Jogo no Fynecine!"
                        class="w-full bg-neutral-900/50 border border-neutral-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-600 transition placeholder:text-neutral-700 font-bold uppercase tracking-tight">
                </div>

                {{-- URL da Imagem Grande --}}
                <div class="space-y-3">
                    <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest pl-1">Big Picture (Imagem do Dropdown)</label>
                    <input type="url" name="big_picture_url" value="{{ old('big_picture_url') }}" placeholder="https://... (1280x720 recomendado)"
                        class="w-full bg-neutral-900/50 border border-neutral-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-600 transition text-xs font-mono placeholder:text-neutral-700">
                    <p class="text-[9px] text-neutral-600 font-bold uppercase pl-1">Aparecerá expandida na central de notificações.</p>
                </div>
            </div>

            {{-- Conteúdo --}}
            <div class="space-y-3">
                <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest pl-1">Conteúdo do Push (Corpo)</label>
                <textarea name="content" rows="3" required placeholder="Escreva a mensagem curta e direta que aparecerá na tela de bloqueio..."
                    class="w-full bg-neutral-900/50 border border-neutral-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-600 transition placeholder:text-neutral-700 font-medium">{{ old('content') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Segmentação --}}
                <div class="space-y-3">
                    <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest pl-1">Segmentação de Disparo</label>
                    <select name="segment" id="segment_select" required
                        class="w-full bg-neutral-900/50 border border-neutral-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-600 transition cursor-pointer font-bold uppercase tracking-tighter">
                        <option value="all" selected>Todos os Dispositivos</option>
                        <option value="premium">Apenas VIPs</option>
                        <option value="basic">Apenas Basic</option>
                        <option value="free">Apenas Free</option>
                        <option value="guest">Até Visitantes</option>
                        <option value="individual">Individual</option>
                    </select>
                </div>

                {{-- User Selector (AJAX Search) --}}
                <div id="user_id_group" class="space-y-3 hidden">
                    <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest pl-1">Buscar Usuário (Nome ou E-mail)</label>
                    <div class="relative">
                        <input type="text" id="user_search_input" placeholder="Pesquisar..."
                            class="w-full bg-neutral-900/50 border border-neutral-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-600 transition font-bold font-mono">
                        
                        <input type="hidden" name="user_id" id="selected_user_id" value="{{ old('user_id') }}">
                        
                        <div id="user_search_results" class="absolute z-50 w-full mt-2 bg-neutral-900 border border-neutral-800 rounded-2xl overflow-hidden hidden shadow-2xl">
                        </div>
                    </div>
                    <div id="selected_user_display" class="hidden flex items-center gap-2 p-2 bg-blue-900/20 border border-blue-900/30 rounded-xl">
                        <img src="" id="selected_user_avatar" class="w-6 h-6 rounded-full object-cover">
                        <span id="selected_user_name" class="text-[10px] font-bold text-blue-400"></span>
                        <button type="button" onclick="clearSelectedUser()" class="ml-auto text-neutral-500 hover:text-white">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 border-t border-neutral-900 pt-8 mt-4">
                {{-- Ação --}}
                <div class="space-y-3">
                    <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest pl-1">Tipo de Ação</label>
                    <select name="action_type" id="action_type" required
                        class="w-full bg-neutral-900/50 border border-neutral-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-600 transition cursor-pointer font-bold uppercase tracking-tighter">
                        <option value="none" selected>Nenhuma Ação</option>
                        <option value="url">Link Externo</option>
                        <option value="movie">Abrir Filme</option>
                        <option value="series">Abrir Série</option>
                        <option value="plans">Abrir Planos</option>
                    </select>
                </div>

                {{-- Valor da Ação --}}
                <div class="space-y-3">
                    <label class="block text-xs font-black text-neutral-500 uppercase tracking-widest pl-1">Slug / ID do Conteúdo</label>
                    <div class="relative">
                        <input type="text" name="action_value" id="action_value" value="{{ old('action_value') }}" placeholder="Busque pelo conteúdo..."
                            class="w-full bg-neutral-900/50 border border-neutral-800 rounded-2xl px-5 py-4 text-white focus:outline-none focus:border-blue-600 transition text-xs font-mono">
                        
                        <div id="search_results" class="absolute z-50 w-full mt-2 bg-neutral-900 border border-neutral-800 rounded-2xl overflow-hidden hidden shadow-2xl">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-10 flex justify-end">
                <button type="submit" class="w-full md:w-auto bg-blue-600 text-white font-black px-16 py-5 rounded-2xl shadow-xl hover:bg-blue-700 transition active:scale-95 flex items-center justify-center gap-4 uppercase tracking-widest">
                    <i class="fa-solid fa-paper-plane animate-bounce"></i> Realizar Disparo FCM
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

    // User Search elements
    const userSearchInput = document.getElementById('user_search_input');
    const userSearchResults = document.getElementById('user_search_results');
    const selectedUserId = document.getElementById('selected_user_id');
    const selectedUserDisplay = document.getElementById('selected_user_display');
    const selectedUserName = document.getElementById('selected_user_name');
    const selectedUserAvatar = document.getElementById('selected_user_avatar');

    segmentSelect.addEventListener('change', () => {
        userGroup.classList.toggle('hidden', segmentSelect.value !== 'individual');
    });

    // User Search AJAX
    let userSearchTimeout;
    userSearchInput.addEventListener('input', () => {
        const query = userSearchInput.value;
        clearTimeout(userSearchTimeout);
        if (query.length < 2) {
            userSearchResults.classList.add('hidden');
            return;
        }

        userSearchTimeout = setTimeout(() => {
            fetch(`{{ route('admin.notifications.search-users') }}?q=${query}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length > 0) {
                        userSearchResults.innerHTML = '';
                        data.forEach(user => {
                            const div = document.createElement('div');
                            div.className = 'p-3 flex items-center gap-3 hover:bg-neutral-800 cursor-pointer transition border-b border-neutral-800/40 last:border-0';
                            div.innerHTML = `
                                <img src="${user.avatar || 'https://ui-avatars.com/api/?name=' + user.name}" class="w-8 h-8 rounded-full object-cover">
                                <div class="flex-1">
                                    <div class="text-white text-xs font-black tracking-tight">${user.name}</div>
                                    <div class="text-neutral-500 text-[9px] font-bold uppercase">${user.email}</div>
                                </div>
                            `;
                            div.onclick = () => {
                                selectUser(user);
                            };
                            userSearchResults.appendChild(div);
                        });
                        userSearchResults.classList.remove('hidden');
                    } else {
                        userSearchResults.classList.add('hidden');
                    }
                });
        }, 300);
    });

    function selectUser(user) {
        selectedUserId.value = user.id;
        selectedUserName.textContent = user.name;
        selectedUserAvatar.src = user.avatar || 'https://ui-avatars.com/api/?name=' + user.name;
        selectedUserDisplay.classList.remove('hidden');
        userSearchResults.classList.add('hidden');
        userSearchInput.value = '';
        userSearchInput.classList.add('hidden');
    }

    function clearSelectedUser() {
        selectedUserId.value = '';
        selectedUserDisplay.classList.add('hidden');
        userSearchInput.classList.remove('hidden');
    }

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
                            div.className = 'p-4 flex items-center gap-4 hover:bg-neutral-800 cursor-pointer transition border-b border-neutral-800/40 last:border-0';
                            div.innerHTML = `
                                <img src="${item.poster || 'https://via.placeholder.com/40x60'}" class="w-10 h-14 object-cover rounded-lg">
                                <div class="flex-1">
                                    <div class="text-white text-sm font-black tracking-tighter">${item.title}</div>
                                    <div class="text-neutral-500 text-[10px] font-bold uppercase">ID: ${item.id}</div>
                                </div>
                            `;
                            div.onclick = () => {
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

    document.addEventListener('click', (e) => {
        if (!searchResults.contains(e.target) && e.target !== actionValue) {
            searchResults.classList.add('hidden');
        }
        if (!userSearchResults.contains(e.target) && e.target !== userSearchInput) {
            userSearchResults.classList.add('hidden');
        }
    });
</script>
@endsection

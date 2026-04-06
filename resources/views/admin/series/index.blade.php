@extends('layouts.admin')

@section('title', 'Series')

@section('content')
    <section>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-white">Catálogo de Séries</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.series.bulk') }}" class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700 transition text-sm text-white font-medium">
                    <i class="fa-solid fa-cloud-arrow-down mr-2"></i>Bulk Import
                </a>
                <button class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
                    <i class="fa-solid fa-plus mr-2"></i>Adicionar Série
                </button>
            </div>
        </div>

        <!-- Form de Pesquisa -->
        <form method="GET" action="{{ route('admin.series.index') }}" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Pesquisar por título ou ano"
                class="px-4 py-2 rounded bg-neutral-800 text-white w-full">
            <button type="submit" class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition">
                Pesquisar
            </button>
        </form>

        <div class="bg-neutral-900 rounded-lg overflow-hidden">
            <div class="table-container">
                <table class="w-full">
                    <thead class="bg-neutral-800">
                        <tr>
                            <th class="text-left p-4">Capa</th>
                            <th class="text-left p-4">ID</th>
                            <th class="text-left p-4">Título</th>
                            <th class="text-left p-4">Ano</th>
                            <th class="text-left p-4">Categoria</th>
                            <th class="text-left p-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($series as $serie)
                            <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                                <td class="p-2">
                                    <img src="{{ $serie->poster_path ? asset($serie->poster_path) : asset('images/placeholder.png') }}"
                                        class="movie-poster rounded">
                                </td>
                                <td class="p-4">{{ $serie->id }}</td>
                                <td class="p-4 font-medium">{{ $serie->name }}</td>
                                <td class="p-4">{{ $serie->first_air_year }}</td>
                                <td class="p-4">
                                    <form action="{{ route('admin.series.category.update', $serie->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <select name="content_category_id" onchange="this.form.submit()" class="bg-neutral-800 text-xs px-2 py-1 border border-neutral-700 rounded text-neutral-300 outline-none">
                                            <option value="">Nenhuma</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ $serie->content_category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </td>
                                <td class="p-4">
                                    <button type="button" onclick="openContentNotificationModal('serie', {{ $serie->id }}, '{{ addslashes($serie->name) }}', '{{ $serie->poster_path }}')" class="text-purple-500 hover:text-purple-400 mr-2" title="Enviar Notificação">
                                        <i class="fa-solid fa-bell"></i>
                                    </button>

                                    <button class="text-blue-500 hover:text-blue-400 mr-2">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>

                                    <a href="{{ route('admin.series.seasons', $serie->id) }}"
                                        class="text-green-500 hover:text-green-400 mr-2" title="Ver temporadas">

                                        <i class="fa-solid fa-layer-group"></i>

                                    </a>

                                    <form action="{{ route('admin.series.delete', $serie->id) }}" method="POST"
                                        class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="text-red-500 hover:text-red-400 swal-delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>

                                    <button type="button" onclick="openTagModal('serie', {{ $serie->id }}, '{{ addslashes($serie->name) }}', '{{ $serie->tag_text }}', '{{ $serie->tag_expires_at ? $serie->tag_expires_at->format('Y-m-d\TH:i') : '' }}')" class="ml-2 text-yellow-500 hover:text-yellow-400" title="Gerenciar Tag">
                                        <i class="fa-solid fa-tag"></i>
                                    </button>

                                    <form action="{{ route('admin.series.tag.update', $serie->id) }}" method="POST" class="inline-block ml-2" onsubmit="return confirm('Remover a tag desta série?')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="tag_text" value="">
                                        <button type="submit" class="text-red-400 hover:text-red-300" title="Limpar Tag">
                                            <i class="fa-solid fa-tag-slash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center p-4">Nenhuma serie encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div class="p-4">
                {{ $series->links() }}
            </div>
        </div>

        <x-swal />
    </section>

{{-- Modal de Notificação para Conteúdo (Filme/Série) --}}
<div id="contentNotificationModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-neutral-900 rounded-lg p-6 max-w-md w-full border border-neutral-800">
        <h3 class="text-lg font-bold text-white mb-4">
            <i class="fa-solid fa-bell mr-2"></i> Enviar Notificação
        </h3>

        <form action="{{ route('admin.notifications.send-content') }}" method="POST" onsubmit="return validateContentNotification()">
            @csrf

            <input type="hidden" id="contentType" name="content_type">
            <input type="hidden" id="contentId" name="content_id">

            <div class="mb-4">
                <div class="flex items-center gap-3 p-3 bg-neutral-800 rounded border border-neutral-700">
                    <img id="contentPoster" src="" class="w-12 h-16 rounded object-cover">
                    <div class="flex-1">
                        <p class="text-xs text-neutral-500">Conteúdo:</p>
                        <p id="contentTitle" class="font-bold text-white text-sm"></p>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-white mb-2">Alvo (Segmentação)</label>
                <select name="segment" class="w-full px-3 py-2 bg-neutral-800 text-white rounded border border-neutral-700 focus:border-purple-500 focus:outline-none" required>
                    <option value="all">🌍 Global (Todos)</option>
                    <option value="premium">💰 Plano Premium</option>
                    <option value="basic">📦 Plano Basic</option>
                    <option value="free">🆓 Plano Free</option>
                    <option value="guest">👻 Convidados</option>
                </select>
            </div>

            <div class="mb-4 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="send_in_app" value="1" class="rounded" checked>
                    <span class="text-sm text-neutral-400"><i class="fa-solid fa-inbox mr-1"></i> Salvar no Histórico</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="send_push" value="1" class="rounded">
                    <span class="text-sm text-neutral-400"><i class="fa-solid fa-mobile-screen mr-1"></i> Enviar Push</span>
                </label>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="button" onclick="closeContentNotificationModal()" class="flex-1 bg-neutral-800 hover:bg-neutral-700 text-white px-4 py-2 rounded transition">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded transition font-bold">
                    <i class="fa-solid fa-paper-plane mr-1"></i> Enviar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openContentNotificationModal(type, id, title, poster) {
        document.getElementById('contentType').value = type;
        document.getElementById('contentId').value = id;
        document.getElementById('contentTitle').textContent = title;
        document.getElementById('contentPoster').src = '/storage/' + poster;
        document.getElementById('contentNotificationModal').classList.remove('hidden');
    }

    function closeContentNotificationModal() {
        document.getElementById('contentNotificationModal').classList.add('hidden');
    }

    function openTagModal(type, id, title, tagText, tagExpires) {
        document.getElementById('tagContentId').value = id;
        document.getElementById('tagContentTitle').textContent = title;
        document.getElementById('tagText').value = tagText || '';
        document.getElementById('tagExpiresAt').value = tagExpires || '';
        
        const form = document.getElementById('tagForm');
        form.action = `/dashzin/series/${id}/tag`;
        
        document.getElementById('tagModal').classList.remove('hidden');
    }

    function closeTagModal() {
        document.getElementById('tagModal').classList.add('hidden');
    }

    function validateContentNotification() {
        const inApp = document.querySelector('#contentNotificationModal input[name="send_in_app"]').checked;
        const push = document.querySelector('#contentNotificationModal input[name="send_push"]').checked;

        if (!inApp && !push) {
            alert('Selecione pelo menos uma opção: In-App ou Push');
            return false;
        }
        return true;
    }

    // Fechar ao clicar fora
    document.getElementById('contentNotificationModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeContentNotificationModal();
    });
    document.getElementById('tagModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeTagModal();
    });
</script>

{{-- Modal de Gerenciar Tag --}}
<div id="tagModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-neutral-900 rounded-lg p-6 max-w-md w-full border border-neutral-800 text-white">
        <h3 class="text-lg font-bold mb-4">
            <i class="fa-solid fa-tag mr-2 text-yellow-500"></i> Gerenciar Tag Promocional
        </h3>

        <form id="tagForm" action="" method="POST">
            @csrf
            @method('PATCH')

            <div class="mb-4">
                <p class="text-xs text-neutral-500 mb-1">Série:</p>
                <p id="tagContentTitle" class="font-bold text-sm"></p>
                <input type="hidden" id="tagContentId">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Texto da Tag (ex: NOVO EPISÓDIO)</label>
                <input type="text" id="tagText" name="tag_text" maxlength="50" placeholder="Deixe vazio para remover"
                       class="w-full px-3 py-2 bg-neutral-800 text-white rounded border border-neutral-700 focus:border-purple-500 focus:outline-none">
                <p class="text-[10px] text-neutral-500 mt-1 italic">* Máximo 50 caracteres. Aparecerá sobre o pôster no App.</p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold mb-2">Expira em (Opcional)</label>
                <input type="datetime-local" id="tagExpiresAt" name="tag_expires_at"
                       class="w-full px-3 py-2 bg-neutral-800 text-white rounded border border-neutral-700 focus:border-purple-500 focus:outline-none">
                <p class="text-[10px] text-neutral-500 mt-1 italic">* Após esta data, a tag deixará de aparecer automaticamente.</p>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="button" onclick="closeTagModal()" class="flex-1 bg-neutral-800 hover:bg-neutral-700 text-white px-4 py-2 rounded transition">
                    Cancelar
                </button>
                <button type="submit" class="flex-1 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded transition font-bold">
                    <i class="fa-solid fa-save mr-1"></i> Salvar Tag
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

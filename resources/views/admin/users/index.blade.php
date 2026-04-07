@extends('layouts.admin')

@section('title', 'Gerenciamento de Usuários')

@section('content')
<section>
    {{-- Título + Botão Novo Usuário + Busca --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold">Usuários Cadastrados</h2>
            <p class="text-xs text-neutral-500">Gerencie permissões, planos e acesso.</p>
        </div>

        <div class="flex flex-col md:flex-row items-center gap-3 w-full md:w-auto">
            {{-- Form de busca --}}
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex items-center gap-2 w-full md:w-auto">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="ID, nome ou email"
                       class="px-3 py-2 rounded bg-neutral-800 text-white border border-neutral-700 focus:outline-none focus:ring-2 focus:ring-netflix text-sm w-full">
                <button type="submit" class="bg-neutral-800 border border-neutral-700 px-4 py-2 rounded hover:bg-neutral-700 transition text-sm">
                    <i class="fa-solid fa-search"></i>
                </button>
            </form>

            {{-- Botão Novo Usuário --}}
            <a href="{{ route('admin.users.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm font-bold w-full md:w-auto text-center">
                <i class="fa-solid fa-plus mr-2"></i>Novo Usuário
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-900/30 border border-green-600 text-green-400 px-4 py-3 rounded relative text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabela de usuários --}}
    <div class="bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-800/50 text-neutral-400">
                    <tr>
                        <th class="p-4 font-semibold uppercase tracking-wider">Usuário</th>
                        <th class="p-4 font-semibold uppercase tracking-wider">Tipo/Acesso</th>
                        <th class="p-4 font-semibold uppercase tracking-wider">Plano</th>
                        <th class="p-4 font-semibold uppercase tracking-wider">Status</th>
                        <th class="p-4 text-right font-semibold uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800">
                    @forelse ($users as $user)
                        <tr class="hover:bg-neutral-800/20 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" class="w-10 h-10 rounded-full border border-neutral-700">
                                    <div>
                                        <p class="font-bold text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-neutral-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                @if ($user->isAdmin())
                                    <span class="bg-blue-600/10 text-blue-500 border border-blue-600/20 px-2 py-0.5 rounded text-xs font-bold">ADMIN</span>
                                @else
                                    <span class="bg-neutral-800 text-neutral-400 border border-neutral-700 px-2 py-0.5 rounded text-xs font-bold">CLIENTE</span>
                                @endif
                                <p class="text-[10px] text-neutral-600 mt-1">ID: #{{ $user->id }}</p>
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $user->isPremium() ? 'bg-yellow-600/10 text-yellow-500 border border-yellow-600/20' : 'bg-neutral-800 text-neutral-500 border border-neutral-700' }}">
                                    {{ strtoupper($user->plan_type ?? 'FREE') }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if ($user->is_banned)
                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-full text-xs font-medium bg-red-900/20 text-red-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Inativo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 py-1 px-2 rounded-full text-xs font-medium bg-green-900/20 text-green-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Ativo
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button type="button" onclick="openUserNotificationModal({{ $user->id }}, '{{ $user->name }}')" class="text-purple-500 hover:text-purple-400 transition" title="Enviar Notificação">
                                        <i class="fa-solid fa-bell"></i>
                                    </button>

                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="text-neutral-400 hover:text-white transition">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    @if($user->banned_at)
                                        <form action="{{ route('admin.users.unban', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-500 hover:text-green-400 transition" title="Remover Ban">
                                                <i class="fa-solid fa-shield-halved"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Banir este usuário impedirá acesso pelo IP e Dispositivo. Continuar?')">
                                            @csrf
                                            <button type="submit" class="text-yellow-600 hover:text-yellow-500 transition" title="Banir Usuário">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Excluir usuário permanentemente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-500 transition">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-8 text-neutral-500">Nenhum usuário encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginação --}}
    <div class="mt-6">
        {{ $users->links() }}
    </div>
</section>

{{-- Modal de Notificação para Usuário --}}
<div id="userNotificationModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-neutral-900 rounded-lg p-6 max-w-md w-full border border-neutral-800">
        <h3 class="text-lg font-bold text-white mb-4">
            <i class="fa-solid fa-bell mr-2"></i> Enviar Notificação
        </h3>

        <form action="{{ route('admin.notifications.send-user') }}" method="POST" onsubmit="return validateUserNotification()">
            @csrf

            <input type="hidden" id="userId" name="user_id">

            <div class="mb-4">
                <label class="block text-sm font-bold text-white mb-2">Usuário</label>
                <input type="text" id="userNameDisplay" class="w-full px-3 py-2 bg-neutral-800 text-white rounded border border-neutral-700 cursor-not-allowed" readonly>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-white mb-2">Título</label>
                <input type="text" name="title" class="w-full px-3 py-2 bg-neutral-800 text-white rounded border border-neutral-700 focus:border-purple-500 focus:outline-none" placeholder="Ex: Promoção Especial" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-white mb-2">Mensagem</label>
                <textarea name="content" rows="3" class="w-full px-3 py-2 bg-neutral-800 text-white rounded border border-neutral-700 focus:border-purple-500 focus:outline-none" placeholder="Digite a mensagem..." required></textarea>
            </div>

            <div class="mb-4 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="send_in_app" value="1" class="rounded" checked>
                    <span class="text-sm text-neutral-400"><i class="fa-solid fa-inbox mr-1"></i> Salvar no Histórico (In-App)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="send_push" value="1" class="rounded">
                    <span class="text-sm text-neutral-400"><i class="fa-solid fa-mobile-screen mr-1"></i> Enviar Push</span>
                </label>
            </div>

            <div class="flex gap-2 mt-6">
                <button type="button" onclick="closeUserNotificationModal()" class="flex-1 bg-neutral-800 hover:bg-neutral-700 text-white px-4 py-2 rounded transition">
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
    function openUserNotificationModal(userId, userName) {
        document.getElementById('userId').value = userId;
        document.getElementById('userNameDisplay').value = userName;
        document.getElementById('userNotificationModal').classList.remove('hidden');
    }

    function closeUserNotificationModal() {
        document.getElementById('userNotificationModal').classList.add('hidden');
    }

    function validateUserNotification() {
        const inApp = document.querySelector('input[name="send_in_app"]').checked;
        const push = document.querySelector('input[name="send_push"]').checked;

        if (!inApp && !push) {
            alert('Selecione pelo menos uma opção: In-App ou Push');
            return false;
        }
        return true;
    }

    // Fechar ao clicar fora
    document.getElementById('userNotificationModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeUserNotificationModal();
    });
</script>
@endsection
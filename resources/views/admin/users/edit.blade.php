@extends('layouts.admin')

@section('title', 'Editar Usuário')

@section('content')
<section class="max-w-4xl">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-white">Editar Usuário: {{ $user->name }}</h2>
        <p class="text-sm text-neutral-400">Altere o plano, permissões ou dados cadastrais.</p>
    </div>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="bg-neutral-900 p-6 rounded-lg border border-neutral-800 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Nome Completo</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 focus:ring-2 focus:ring-netflix outline-none">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 focus:ring-2 focus:ring-netflix outline-none">
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Tipo de Acesso</label>
                <select name="is_admin" class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 focus:ring-2 focus:ring-netflix outline-none">
                    <option value="0" {{ $user->is_admin ? '' : 'selected' }}>Cliente (App/Site)</option>
                    <option value="1" {{ $user->is_admin ? 'selected' : '' }}>Administrador (Painel)</option>
                </select>
                @error('is_admin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Plano de Assinatura</label>
                <select name="plan_type" class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 focus:ring-2 focus:ring-netflix outline-none">
                    <option value="free" {{ $user->plan_type === 'free' ? 'selected' : '' }}>Free (Grátis)</option>
                    <option value="basic" {{ $user->plan_type === 'basic' ? 'selected' : '' }}>Basic (Básico)</option>
                    <option value="premium" {{ $user->plan_type === 'premium' ? 'selected' : '' }}>Premium (VIP)</option>
                </select>
                @error('plan_type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-1 gap-6">
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Nova Senha (deixe em branco para não alterar)</label>
                <input type="password" name="password" 
                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 focus:ring-2 focus:ring-netflix outline-none"
                       placeholder="Mínimo 6 caracteres">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="pt-4 flex items-center gap-4">
            <button type="submit" class="bg-netflix px-8 py-2.5 rounded text-white font-bold hover:bg-red-700 transition">
                Salvar Alterações
            </button>
            <a href="{{ route('admin.users.index') }}" class="text-neutral-400 hover:text-white transition text-sm">
                Cancelar e Voltar
            </a>
        </div>
    </form>

    <div class="mt-8">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white">Dispositivos e Sessões</h3>
                <p class="text-sm text-neutral-400">Gerencie onde este usuário está logado e realize banimentos de hardware.</p>
            </div>
            <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" onsubmit="return confirm('Isso banirá a conta e todos os dispositivos associados permanentemente. Continuar?')">
                @csrf
                @if($user->banned_at)
                    <a href="{{ route('admin.users.unban', $user->id) }}" class="bg-green-600 px-4 py-2 rounded text-white text-sm font-bold hover:bg-green-700 transition">
                        Remover Banimento da Conta
                    </a>
                @else
                    <button type="submit" class="bg-red-600 px-4 py-2 rounded text-white text-sm font-bold hover:bg-red-700 transition">
                        Banir Usuário e Todos os Aparelhos
                    </button>
                @endif
            </form>
        </div>

        <div class="bg-neutral-900 border border-neutral-800 rounded-lg overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-neutral-800 bg-neutral-950 text-neutral-400">
                        <th class="px-6 py-4 font-medium">Aparelho / Sistema</th>
                        <th class="px-6 py-4 font-medium">IP / Localização</th>
                        <th class="px-6 py-4 font-medium">Último Acesso</th>
                        <th class="px-6 py-4 font-medium text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800 text-white">
                    @forelse($tokens as $token)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-bold">{{ $token->device_name ?: 'Desconhecido' }}</div>
                                <div class="text-xs text-neutral-500 uppercase">{{ $token->device_type ?: 'mobile' }}</div>
                                <div class="text-[10px] text-neutral-600 break-all">{{ $token->device_uuid }}</div>
                            </td>
                            <td class="px-6 py-4 text-neutral-300">
                                <div>{{ $token->ip_address ?: '0.0.0.0' }}</div>
                                <div class="text-xs text-neutral-500">{{ $token->location ?: 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 text-neutral-400">
                                {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Nunca usado' }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <div class="flex justify-end gap-2">
                                    {{-- Desconectar --}}
                                    <form action="{{ route('admin.users.revoke-token', [$user->id, $token->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" title="Desconectar / Encerrar Sessão" class="p-2 bg-neutral-800 hover:bg-neutral-700 rounded text-neutral-400 hover:text-white transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Banir UUID --}}
                                    @if($token->device_uuid)
                                        <form action="{{ route('admin.users.ban-device', $user->id) }}" method="POST" onsubmit="return confirm('Deseja banir este hardware permanentemente? O usuário não conseguirá acessar por este aparelho nem com outra conta.')">
                                            @csrf
                                            <input type="hidden" name="device_uuid" value="{{ $token->device_uuid }}">
                                            <button type="submit" title="Banir Aparelho (UUID)" class="p-2 bg-red-900/30 hover:bg-red-600 rounded text-red-500 hover:text-white transition border border-red-900/50">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-neutral-500 italic">
                                Nenhuma sessão ativa encontrada para este usuário.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

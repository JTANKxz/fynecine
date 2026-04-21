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
            @if(Auth::user()->canChangeUserSensitiveData())
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Cargo / Permissão</label>
                <select name="role" class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 focus:ring-2 focus:ring-netflix outline-none">
                    <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Cliente (App/Site)</option>
                    <option value="editor" {{ $user->role === 'editor' ? 'selected' : '' }}>Editor (Gerencia Conteúdo)</option>
                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador (Total)</option>
                </select>
                @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            @endif

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

        @if(Auth::user()->canChangeUserSensitiveData())
        <div class="grid md:grid-cols-1 gap-6">
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Nova Senha (deixe em branco para não alterar)</label>
                <input type="password" name="password" 
                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 focus:ring-2 focus:ring-netflix outline-none"
                       placeholder="Mínimo 6 caracteres">
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        @endif

        <div class="pt-4 flex items-center gap-4">
            <button type="submit" class="bg-netflix px-8 py-2.5 rounded text-white font-bold hover:bg-red-700 transition">
                Salvar Alterações
            </button>
            <a href="{{ route('admin.users.index') }}" class="text-neutral-400 hover:text-white transition text-sm">
                Cancelar e Voltar
            </a>
        </div>
    </form>

    </form>

    {{-- Gerenciamento de Perfis --}}
    <div class="mt-8 bg-neutral-900 p-6 rounded-lg border border-neutral-800">
        <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <i class="fa-solid fa-users text-blue-500"></i> Perfis do Usuário
        </h3>
        <p class="text-sm text-neutral-400 mb-6 font-medium italic">Configure o PIN e acesso ao modo adulto por perfil.</p>

        <form action="{{ route('admin.users.profiles.update', $user->id) }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="space-y-4">
                @foreach($user->profiles as $profile)
                <div class="bg-neutral-950 p-4 rounded-xl border border-neutral-800 hover:border-neutral-700 transition flex flex-col md:flex-row md:items-center justify-between gap-6 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-netflix/20 border border-netflix/30 flex items-center justify-center overflow-hidden">
                            @if($profile->avatar)
                                <img src="{{ $profile->avatar }}" class="w-full h-full object-cover">
                            @else
                                <i class="fa-solid fa-user text-netflix text-xl"></i>
                            @endif
                        </div>
                        <div>
                            <div class="font-bold text-white text-lg">{{ $profile->name }}</div>
                            <div class="text-[10px] text-neutral-500 uppercase tracking-widest">{{ $profile->id }}</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-6">
                        {{-- Modo Adulto --}}
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-neutral-500 uppercase">Modo Adulto</span>
                            <label class="relative inline-flex items-center cursor-pointer scale-90 -ml-1">
                                <input type="checkbox" name="profiles[{{ $profile->id }}][is_adult_enabled]" value="1" {{ $profile->is_adult_enabled ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                            </label>
                        </div>

                        {{-- PIN Adulto --}}
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] font-bold text-neutral-500 uppercase">PIN Adulto (4 Dígitos)</span>
                            <input type="text" name="profiles[{{ $profile->id }}][adult_pin]" value="{{ $profile->adult_pin }}" 
                                   maxlength="4" placeholder="0000"
                                   class="bg-black border border-neutral-800 text-purple-400 font-mono text-sm rounded px-3 py-1.5 focus:border-purple-500 outline-none w-24 text-center">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($user->profiles->count() > 0)
            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition transform active:scale-95 shadow-lg shadow-blue-900/20">
                    <i class="fa-solid fa-save mr-2"></i> Atualizar Configurações de Perfis
                </button>
            </div>
            @else
            <p class="text-center text-neutral-500 py-4 italic">Nenhum perfil criado para este usuário ainda.</p>
            @endif
        </form>
    </div>

    @if(Auth::user()->isAdmin())
    <div class="mt-8">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white">Dispositivos e Sessões</h3>
                <p class="text-sm text-neutral-400">Gerencie onde este usuário está logado e realize banimentos de hardware.</p>
            </div>
            @if($user->is_banned)
                <form action="{{ route('admin.users.unban', $user->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 px-4 py-2 rounded text-white text-sm font-bold hover:bg-green-700 transition">
                        Ativar / Remover Banimento da Conta
                    </button>
                </form>
            @else
                <form action="{{ route('admin.users.ban', $user->id) }}" method="POST" onsubmit="return confirm('Isso banirá a conta e todos os dispositivos associados permanentemente. Continuar?')">
                    @csrf
                    <button type="submit" class="bg-red-600 px-4 py-2 rounded text-white text-sm font-bold hover:bg-red-700 transition">
                        Banir Usuário e Todos os Aparelhos
                    </button>
                </form>
            @endif
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
                                <div class="flex items-center gap-2">
                                    <div class="font-bold">{{ $token->device_name ?: 'Desconhecido' }}</div>
                                    @if($token->device_uuid && in_array($token->device_uuid, $bannedUuids))
                                        <span class="py-0.5 px-2 bg-red-600 text-[10px] font-bold rounded uppercase">Hardware Banido</span>
                                    @endif
                                </div>
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

                                    {{-- Banir / Desbanir UUID --}}
                                    @if($token->device_uuid)
                                        @if(in_array($token->device_uuid, $bannedUuids))
                                            <form action="{{ route('admin.users.unban-device', [$user->id, $token->device_uuid]) }}" method="POST">
                                                @csrf
                                                <button type="submit" title="Desbloquear Aparelho (Unban UUID)" class="p-2 bg-green-900/30 hover:bg-green-600 rounded text-green-500 hover:text-white transition border border-green-900/50">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
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
    @endif
</section>
@endsection

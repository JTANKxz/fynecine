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
</section>
@endsection

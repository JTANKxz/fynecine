@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<section>
    {{-- Título + Botão Novo Usuário + Busca --}}
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Usuários Cadastrados</h2>

        <div class="flex items-center gap-2">
            {{-- Form de busca --}}
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por ID, nome ou email"
                       class="px-3 py-1 rounded bg-neutral-800 text-white border border-neutral-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
                    Buscar
                </button>
            </form>

            {{-- Botão Novo Usuário --}}
            <button class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
                <i class="fa-solid fa-plus mr-2"></i>Novo Usuário
            </button>
        </div>
    </div>

    {{-- Tabela de usuários --}}
    <div class="bg-neutral-900 rounded-lg overflow-hidden">
        <div class="table-container">
            <table class="w-full">
                <thead class="bg-neutral-800">
                    <tr>
                        <th class="text-left p-4">ID</th>
                        <th class="text-left p-4">Nome</th>
                        <th class="text-left p-4">Email</th>
                        <th class="text-left p-4">Tipo</th>
                        <th class="text-left p-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                            <td class="p-4">{{ $user->id }}</td>
                            <td class="p-4">{{ $user->name }}</td>
                            <td class="p-4">{{ $user->email }}</td>
                            <td class="p-4">
                                @if ($user->isAdmin())
                                    <span class="bg-blue-600/20 text-blue-500 px-2 py-1 rounded text-sm">Admin</span>
                                @else
                                    <span class="bg-gray-200 text-gray-600 px-2 py-1 rounded text-sm">User</span>
                                @endif
                            </td>
                            <td class="p-4 flex items-center gap-2">
                                {{-- Botão editar --}}
                                {{-- <a href="{{ route('users.edit', $user->id) }}" class="text-blue-500 hover:text-blue-400">
                                    <i class="fa-solid fa-edit"></i>
                                </a> --}}

                                {{-- Botão deletar --}}
                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="text-red-500 hover:text-red-400 swal-delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-4">Nenhum usuário encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginação --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <x-swal />
</section>
@endsection
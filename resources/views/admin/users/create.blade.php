@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
    <section>
        <h2 class="text-xl font-bold mb-4">Cadastro de Usuário</h2>
        <form action="{{ route('admin.users.store') }}" method="POST" class="bg-neutral-900 p-5 rounded space-y-4">
            @csrf
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Nome</label>
                    <input type="text" name="name"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="João Silva">
                </div>
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Username</label>
                    <input type="text" name="username"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="joaosilva">
                </div>
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Email</label>
                    <input type="email" name="email"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="joao@email.com">
                </div>
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Senha</label>
                    <input type="password" name="password"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                        placeholder="********">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">Tipo de usuário</label>
                    <select name="is_admin"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                        <option value="0" selected>Usuário comum</option>
                        <option value="1">Administrador</option>
                        {{-- <option>Editor</option> --}}
                        {{-- <option>Convidado</option> --}}
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">Salvar usuário</button>
                <button class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">Cancelar</button>
            </div>
        </form>
    </section>
@endsection

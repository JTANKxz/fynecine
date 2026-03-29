@extends('layouts.admin')

@section('title', 'Categorias de Avatar')

@section('content')
<section>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Categorias de Avatar</h2>
        <a href="{{ route('admin.avatar-categories.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
            <i class="fa-solid fa-plus mr-2"></i>Nova Categoria
        </a>
    </div>

    <form method="GET" action="{{ route('admin.avatar-categories.index') }}" class="mb-4 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Pesquisar por nome da categoria"
               class="px-4 py-2 rounded bg-neutral-800 text-white w-full">
        <button type="submit" class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition">
            Pesquisar
        </button>
    </form>

    @if (session('success'))
        <div class="mb-4 bg-green-900 border border-green-600 text-green-100 px-4 py-2 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-900 rounded-lg overflow-hidden">
        <div class="table-container">
            <table class="w-full">
                <thead class="bg-neutral-800">
                    <tr>
                        <th class="text-left p-4">ID</th>
                        <th class="text-left p-4">Nome</th>
                        <th class="text-left p-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                            <td class="p-4">{{ $category->id }}</td>
                            <td class="p-4 font-medium">{{ $category->name }}</td>
                            <td class="p-4">
                                <a href="{{ route('admin.avatar-categories.edit', $category->id) }}" class="text-blue-500 hover:text-blue-400 mr-2">
                                    <i class="fa-solid fa-edit"></i>
                                </a>

                                <form action="{{ route('admin.avatar-categories.destroy', $category->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-400">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center p-4">Nenhuma categoria encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $categories->links() }}
        </div>
    </div>
</section>
@endsection

@extends('layouts.admin')

@section('title', 'Todos os Avatares')

@section('content')
<section>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Gerenciamento de Avatares</h2>
        <a href="{{ route('admin.avatars.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
            <i class="fa-solid fa-plus mr-2"></i>Novo Avatar
        </a>
    </div>

    <!-- Filtro -->
    <div class="bg-neutral-900 p-4 rounded-lg border border-neutral-800 mb-6">
        <form method="GET" action="{{ route('admin.avatars.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Filtrar por Categoria</label>
                <select name="category_id" class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 text-sm focus:ring-2 focus:ring-netflix outline-none">
                    <option value="">Todas as Categorias</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition text-sm font-bold">
                Filtrar
            </button>
            @if(request('category_id'))
                <a href="{{ route('admin.avatars.index') }}" class="bg-neutral-800 px-6 py-2 rounded hover:bg-neutral-700 transition text-sm font-bold">
                    Limpar
                </a>
            @endif
        </form>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-900 border border-green-600 text-green-100 px-4 py-2 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
        @forelse ($avatars as $avatar)
            <div class="bg-neutral-900 border border-neutral-800 rounded-xl overflow-hidden group relative">
                <div class="aspect-square bg-neutral-800">
                    <img src="{{ $avatar->image_url }}" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <div class="p-3">
                    <p class="text-[10px] text-netflix uppercase font-bold text-center">{{ $avatar->category->name }}</p>
                </div>
                
                <!-- Overlay Ações -->
                <div class="absolute inset-x-0 bottom-0 bg-black/80 p-2 flex justify-around opacity-0 group-hover:opacity-100 transition-opacity">
                    <a href="{{ route('admin.avatars.edit', $avatar->id) }}" class="text-blue-500 hover:text-blue-400">
                        <i class="fa-solid fa-edit"></i>
                    </a>

                    <form action="{{ route('admin.avatars.destroy', $avatar->id) }}" method="POST" class="inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-400">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full py-10 text-center text-neutral-500">
                Nenhum avatar encontrado. Clique em "Novo Avatar" para adicionar.
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $avatars->links() }}
    </div>
</section>
@endsection

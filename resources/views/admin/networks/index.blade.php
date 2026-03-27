@extends('layouts.admin')

@section('title', 'Networks')

@section('content')
<section>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Networks</h2>
        <a href="{{ route('admin.networks.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
            <i class="fa-solid fa-plus mr-2"></i>Nova Network
        </a>
    </div>

    <form method="GET" action="{{ route('admin.networks.index') }}" class="mb-4 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Pesquisar por nome"
               class="px-4 py-2 rounded bg-neutral-800 text-white w-full">
        <button type="submit" class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition">Pesquisar</button>
    </form>

    @if (session('success'))
        <div class="mb-4 bg-green-900 border border-green-600 text-green-100 px-4 py-2 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse ($networks as $network)
            <div class="bg-neutral-900 border border-neutral-800 rounded-lg overflow-hidden group hover:border-neutral-600 transition">
                @if($network->image_url)
                    <img src="{{ $network->image_url }}" class="w-full h-24 object-contain bg-neutral-800 p-3">
                @else
                    <div class="w-full h-24 bg-neutral-800 flex items-center justify-center">
                        <span class="text-lg font-bold text-neutral-500">{{ strtoupper(substr($network->name, 0, 3)) }}</span>
                    </div>
                @endif

                <div class="p-3">
                    <p class="font-bold text-sm truncate">{{ $network->name }}</p>
                    <p class="text-xs text-neutral-500">{{ $network->slug }}</p>

                    <div class="flex items-center gap-2 mt-2">
                        <a href="{{ route('admin.networks.content', $network->id) }}" class="text-purple-400 hover:text-purple-300 text-xs">
                            <i class="fa-solid fa-film mr-1"></i>Conteúdo
                        </a>
                        <a href="{{ route('admin.networks.edit', $network->id) }}" class="text-blue-400 hover:text-blue-300 text-xs">
                            <i class="fa-solid fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.networks.delete', $network->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="text-red-400 hover:text-red-300 text-xs swal-delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-neutral-900 rounded-lg p-8 text-center text-neutral-500">
                Nenhuma network cadastrada.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $networks->links() }}</div>

    <x-swal />
</section>
@endsection

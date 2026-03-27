@extends('layouts.admin')

@section('title', 'Canais de TV')

@section('content')
<section>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Canais de TV ao Vivo</h2>
        <a href="{{ route('admin.channels.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
            <i class="fa-solid fa-plus mr-2"></i>Novo Canal
        </a>
    </div>

    <!-- Form de Pesquisa -->
    <form method="GET" action="{{ route('admin.channels.index') }}" class="mb-4 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Pesquisar por nome do canal"
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
                        <th class="text-left p-4">Logo</th>
                        <th class="text-left p-4">ID</th>
                        <th class="text-left p-4">Nome</th>
                        <th class="text-left p-4">Categorias</th>
                        <th class="text-left p-4">Links</th>
                        <th class="text-left p-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($channels as $channel)
                        <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                            <td class="p-2">
                                @if($channel->image_url)
                                    <img src="{{ $channel->image_url }}" class="w-12 h-12 object-contain rounded bg-neutral-800 p-1">
                                @else
                                    <div class="w-12 h-12 bg-neutral-800 rounded flex items-center justify-center">
                                        <i class="fa-solid fa-tv text-neutral-500"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="p-4">{{ $channel->id }}</td>
                            <td class="p-4 font-medium">{{ $channel->name }}</td>
                            <td class="p-4">
                                @foreach($channel->categories as $cat)
                                    <span class="bg-neutral-700 text-neutral-300 px-2 py-0.5 rounded text-xs mr-1">{{ $cat->name }}</span>
                                @endforeach
                                @if($channel->categories->isEmpty())
                                    <span class="text-neutral-500 text-xs">Sem categoria</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <a href="{{ route('admin.channels.links', $channel->id) }}" class="text-blue-500 hover:text-blue-400">
                                    <i class="fa-solid fa-link mr-1"></i>{{ $channel->links()->count() }}
                                </a>
                            </td>
                            <td class="p-4">
                                <a href="{{ route('admin.channels.edit', $channel->id) }}" class="text-blue-500 hover:text-blue-400 mr-2">
                                    <i class="fa-solid fa-edit"></i>
                                </a>

                                <form action="{{ route('admin.channels.delete', $channel->id) }}" method="POST" class="inline-block">
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
                            <td colspan="6" class="text-center p-4">Nenhum canal encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $channels->links() }}
        </div>
    </div>

    <x-swal />
</section>
@endsection

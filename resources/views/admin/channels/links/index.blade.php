@extends('layouts.admin')

@section('title', 'Links do Canal')

@section('content')
    <section>
        <div class="flex justify-between items-center mb-4">
            <div>
                <h2 class="text-xl font-bold">Links: {{ $channel->name }}</h2>
                <p class="text-xs text-neutral-500">Gerencie os players deste canal</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.channels.links.create', $channel->id) }}"
                    class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
                    <i class="fa-solid fa-plus mr-2"></i>Novo Link
                </a>
                <a href="{{ route('admin.channels.index') }}"
                    class="bg-neutral-700 px-4 py-2 rounded hover:bg-neutral-600 transition text-sm">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Voltar
                </a>
            </div>
        </div>

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
                            <th class="text-left p-4">URL</th>
                            <th class="text-left p-4">Tipo</th>
                            <th class="text-left p-4">Ordem</th>
                            <th class="text-left p-4">Subscription</th>
                            <th class="text-left p-4">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($links as $link)
                            <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                                <td class="p-4">{{ $link->id }}</td>
                                <td class="p-4">{{ $link->name }}</td>
                                <td class="p-4 max-w-xs truncate">{{ $link->url }}</td>
                                <td class="p-4">{{ strtoupper($link->type) }}</td>
                                <td class="p-4">{{ $link->order }}</td>
                                <td class="p-4">
                                    @if ($link->player_sub === 'premium')
                                        <span class="bg-yellow-600/20 text-yellow-400 px-2 py-1 rounded text-sm">Premium</span>
                                    @else
                                        <span class="bg-green-600/20 text-green-400 px-2 py-1 rounded text-sm">Free</span>
                                    @endif
                                </td>
                                <td class="p-4 flex items-center gap-3">
                                    <a href="{{ route('admin.channels.links.edit', $link->id) }}"
                                        class="text-blue-500 hover:text-blue-400">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>

                                    <form action="{{ route('admin.channels.links.delete', $link->id) }}" method="POST">
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
                                <td colspan="7" class="text-center p-6 text-neutral-400">
                                    Nenhum link encontrado para este canal.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <x-swal />
    </section>
@endsection

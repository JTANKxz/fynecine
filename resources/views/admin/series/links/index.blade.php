@extends('layouts.admin')

@section('title', 'Links do Episódio')

@section('content')
    <section>

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">
                Links - Episódio {{ $episode->episode_number }}
            </h2>

            <div class="flex items-center gap-2">

                <a href="{{ route('admin.series.episodes.links.create', $episode->id) }}"
                    class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
                    <i class="fa-solid fa-plus mr-2"></i>Novo Link
                </a>

            </div>
        </div>

        <div class="bg-neutral-900 rounded-lg overflow-hidden">

            <div class="table-container">

                <table class="w-full">

                    <thead class="bg-neutral-800">
                        <tr>
                            <th class="text-left p-4">ID</th>
                            <th class="text-left p-4">Nome</th>
                            <th class="text-left p-4">Url</th>
                            <th class="text-left p-4">Qualidade</th>
                            <th class="text-left p-4">Tipo</th>
                            <th class="text-left p-4">Order</th>
                            <th class="text-left p-4">Subscription</th>
                            <th class="text-left p-4">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($links as $link)
                            <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">

                                <td class="p-4">
                                    {{ $link->id }}
                                </td>

                                <td class="p-4">
                                    {{ $link->name }}
                                </td>

                                <td class="p-4 max-w-xs truncate">
                                    {{ $link->url }}
                                </td>

                                <td class="p-4">
                                    {{ $link->quality }}
                                </td>

                                <td class="p-4">
                                    {{ strtoupper($link->type) }}
                                </td>

                                <td class="p-4">
                                    {{ $link->order }}
                                </td>

                                <td class="p-4">

                                    @if ($link->player_sub === 'premium')
                                        <span class="bg-blue-600/20 text-blue-400 px-2 py-1 rounded text-sm">
                                            Premium
                                        </span>
                                    @else
                                        <span class="bg-green-600/20 text-green-400 px-2 py-1 rounded text-sm">
                                            Free
                                        </span>
                                    @endif

                                </td>

                                <td class="p-4 flex items-center gap-3">

                                    {{-- editar --}}
                                    <a href="{{ route('admin.series.episodes.links.edit', $link->id) }}"
                                        class="text-blue-500 hover:text-blue-400">

                                        <i class="fa-solid fa-edit"></i>

                                    </a>

                                    {{-- deletar --}}
                                    <form action="{{ route('admin.series.episodes.links.delete', $link->id) }}" method="POST">

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
                                <td colspan="8" class="text-center p-6 text-neutral-400">
                                    Nenhum link encontrado
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="p-4">
                {{ $links->links() }}
            </div>

        </div>

        <x-swal />

    </section>
@endsection

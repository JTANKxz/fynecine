@extends('layouts.admin')

@section('title', 'Sliders')

@section('content')
    <section>

        {{-- Título + Botão Novo Slider --}}
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Sliders Cadastrados</h2>

            <div class="flex items-center gap-2">

                <a href="{{ route('admin.sliders.create') }}"
                    class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
                    <i class="fa-solid fa-plus mr-2"></i>Novo Slider
                </a>

            </div>
        </div>

        {{-- Tabela --}}
        <div class="bg-neutral-900 rounded-lg overflow-hidden">
            <div class="table-container">

                <table class="w-full">
                    <thead class="bg-neutral-800">
                        <tr>
                            <th class="text-left p-4">ID</th>
                            <th class="text-left p-4">Backdrop</th>
                            <th class="text-left p-4">Título</th>
                            <th class="text-left p-4">Tipo</th>
                            <th class="text-left p-4">Rating</th>
                            <th class="text-left p-4">Ano</th>
                            <th class="text-left p-4">Posição</th>
                            <th class="text-left p-4">Ações</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse ($sliders as $slider)
                            @php
                                $content = $slider->content;
                            @endphp

                            <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">

                                <td class="p-4">{{ $slider->id }}</td>

                                {{-- backdrop --}}
                                <td class="p-4">
                                    @if ($content && $content->backdrop_path)
                                        <img src="{{ $content->backdrop_path }}" class="w-40 rounded">
                                    @endif
                                </td>

                                {{-- título --}}
                                <td class="p-4 font-semibold">
                                    {{ $slider->content_type == 'movie' ? $content->title : $content->name }}
                                </td>

                                {{-- tipo --}}
                                <td class="p-4">

                                    @if ($slider->content_type == 'movie')
                                        <span class="bg-blue-600/20 text-blue-400 px-2 py-1 rounded text-sm">
                                            Filme
                                        </span>
                                    @else
                                        <span class="bg-green-600/20 text-green-400 px-2 py-1 rounded text-sm">
                                            Série
                                        </span>
                                    @endif

                                </td>

                                {{-- rating --}}
                                <td class="p-4">
                                    ⭐ {{ $content->rating ?? '-' }}
                                </td>

                                {{-- ano --}}
                                <td class="p-4">
                                    {{ $slider->content_type == 'movie' ? $content->release_year : $content->first_air_year }}
                                </td>

                                {{-- posição --}}
                                <td class="p-4">
                                    {{ $slider->position }}
                                </td>

                                {{-- ações --}}
                                <td class="p-4 flex items-center gap-2">

                                    {{-- deletar --}}
                                    <form action="{{ route('admin.sliders.delete', $slider->id) }}" method="POST"
                                        class="inline-block">

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
                                <td colspan="8" class="text-center p-4">
                                    Nenhum slider encontrado.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>
        </div>

        {{-- Paginação --}}
        <div class="mt-4">
            {{ $sliders->links() }}
        </div>

        <x-swal />

    </section>
@endsection

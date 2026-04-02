@extends('layouts.admin')

@section('title', 'Sliders')

@section('content')
    <section>

        {{-- Título + Botão Novo Slider --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-white tracking-tight">Sliders</h1>
                <p class="text-neutral-400 mt-1">Gerencie os destaques principais de cada página.</p>
            </div>
            <a href="{{ route('admin.sliders.create') }}" 
               class="bg-netflix hover:bg-netflix/90 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-netflix/20 transition-all active:scale-95 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                Novo Slider
            </a>
        </div>

        {{-- Filtros de Categoria --}}
        <div class="mb-8 flex items-center gap-3 overflow-x-auto pb-2">
            <a href="{{ route('admin.sliders.index') }}" 
               class="px-5 py-2.5 rounded-full text-sm font-bold transition-all {{ !request('category_id') ? 'bg-netflix text-white shadow-lg shadow-netflix/20' : 'bg-neutral-800 text-neutral-400 hover:bg-neutral-700' }}">
                Home
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('admin.sliders.index', ['category_id' => $cat->id]) }}" 
                   class="px-5 py-2.5 rounded-full text-sm font-bold transition-all {{ request('category_id') == $cat->id ? 'bg-netflix text-white shadow-lg shadow-netflix/20' : 'bg-neutral-800 text-neutral-400 hover:bg-neutral-700' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
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

                                        {{-- editar --}}
                                        <a href="{{ route('admin.sliders.edit', $slider->id) }}" class="text-blue-500 hover:text-blue-400">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>

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

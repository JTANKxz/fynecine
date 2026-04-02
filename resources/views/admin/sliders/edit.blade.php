@extends('layouts.admin')

@section('title', 'Editar Slider')

@section('content')
    <section>

        <h2 class="text-xl font-bold mb-4">Editar Item do Slider</h2>

        <div class="mb-6 bg-neutral-900 p-4 rounded flex items-center gap-4 border border-neutral-800">
            @php
                $content = $slider->content;
            @endphp
            @if ($content)
                <img src="{{ $content->backdrop_path }}" class="w-48 rounded shadow-lg">
                <div>
                    <h3 class="font-bold text-xl text-white">{{ $slider->content_type == 'movie' ? $content->title : $content->name }}</h3>
                    <p class="text-neutral-400 mt-1 uppercase text-xs font-bold tracking-widest">
                        {{ $slider->content_type == 'movie' ? 'Filme' : 'Série' }} • {{ $slider->content_type == 'movie' ? $content->release_year : $content->first_air_year }}
                    </p>
                </div>
            @endif
        </div>

        <form action="{{ route('admin.sliders.update', $slider->id) }}" method="POST" class="bg-neutral-900 p-8 rounded-2xl border border-neutral-800 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid md:grid-cols-2 gap-6">

                {{-- Categoria --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-neutral-400">Página de Exibição</label>
                    <select name="content_category_id" class="w-full p-3 bg-neutral-800 rounded-xl border border-neutral-700 focus:ring-2 focus:ring-netflix outline-none transition-all">
                        <option value="">Home (Geral)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $slider->content_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- posição --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-neutral-400">Posição (Ordem)</label>
                    <input type="number" name="position" value="{{ $slider->position }}"
                        class="w-full p-3 bg-neutral-800 rounded-xl border border-neutral-700 focus:ring-2 focus:ring-netflix outline-none transition-all">
                </div>

            </div>

            {{-- botões --}}
            <div class="flex gap-4 pt-4">
                <button class="bg-netflix text-white px-8 py-3 rounded-xl font-bold hover:bg-netflix/90 transition shadow-lg shadow-netflix/20 active:scale-95">
                    Atualizar Slider
                </button>

                <a href="{{ route('admin.sliders.index', ['category_id' => $slider->content_category_id]) }}"
                    class="bg-neutral-800 text-neutral-300 px-8 py-3 rounded-xl font-bold hover:bg-neutral-700 transition active:scale-95">
                    Cancelar
                </a>
            </div>

        </form>

    </section>
@endsection

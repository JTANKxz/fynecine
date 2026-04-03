@extends('layouts.admin')

@section('title', 'Seções da Home')

@section('content')
<section>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">
                @if($currentCategory)
                    Customizando Página: <span class="text-netflix">{{ $currentCategory->name }}</span>
                @else
                    Customizando Página: <span class="text-netflix">Página Inicial (Geral)</span>
                @endif
            </h1>
            <p class="text-neutral-400 mt-1">Configure o topo (Sliders) e as fileiras de conteúdo desta página.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sliders.create', ['category_id' => request('category_id')]) }}" 
               class="bg-neutral-800 hover:bg-neutral-700 text-white px-5 py-3 rounded-xl font-bold transition-all active:scale-95 flex items-center gap-2 border border-neutral-700">
                <i class="fa-solid fa-images"></i>
                Novo Slider
            </a>
            <a href="{{ route('admin.sections.create', ['category_id' => request('category_id')]) }}" 
               class="bg-netflix hover:bg-netflix/90 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-netflix/20 transition-all active:scale-95 flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                Nova Seção
            </a>
        </div>
    </div>

    {{-- Filtros de Página --}}
    <div class="mb-10 flex items-center gap-3 overflow-x-auto pb-2">
        <a href="{{ route('admin.sections.index') }}" 
           class="px-5 py-2.5 rounded-full text-sm font-bold transition-all {{ !request('category_id') ? 'bg-netflix text-white shadow-lg shadow-netflix/20' : 'bg-neutral-900 border border-neutral-800 text-neutral-400 hover:bg-neutral-800' }}">
            Home (Geral)
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('admin.sections.index', ['category_id' => $cat->id]) }}" 
               class="px-5 py-2.5 rounded-full text-sm font-bold transition-all {{ request('category_id') == $cat->id ? 'bg-netflix text-white shadow-lg shadow-netflix/20' : 'bg-neutral-900 border border-neutral-800 text-neutral-400 hover:bg-neutral-800' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-900/30 border border-green-600/50 text-green-100 px-4 py-3 rounded-xl text-sm flex items-center gap-3">
            <i class="fa-solid fa-check-circle text-green-400"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- BLOCO DE SLIDERS --}}
    <div class="mb-12">
        <div class="flex items-center gap-2 mb-4">
            <i class="fa-solid fa-images text-netflix text-xl"></i>
            <h2 class="text-xl font-bold text-white uppercase tracking-wider">Sliders (Destaques do Topo)</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse ($sliders as $slider)
                <div class="group relative bg-neutral-950 border border-neutral-900 rounded-2xl overflow-hidden hover:border-netflix/50 transition-all duration-300">
                    <img src="{{ $slider->image_url }}" alt="Slider" class="w-full aspect-video object-cover opacity-60 group-hover:opacity-100 transition-opacity">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                    
                    <div class="absolute bottom-4 left-4 right-4 flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold text-netflix uppercase">Posição {{ $slider->position }}</span>
                            <h4 class="text-white font-bold truncate">{{ $slider->title ?? 'Sem Título' }}</h4>
                        </div>
                        <div class="flex items-center gap-2 translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all">
                            <a href="{{ route('admin.sliders.edit', $slider->id) }}" class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-500 transition shadow-lg">
                                <i class="fa-solid fa-edit text-xs"></i>
                            </a>
                            <form action="{{ route('admin.sliders.delete', $slider->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 bg-red-600 rounded-full flex items-center justify-center hover:bg-red-500 transition shadow-lg">
                                    <i class="fa-solid fa-trash text-xs text-white"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full border-2 border-dashed border-neutral-800 rounded-2xl p-8 text-center text-neutral-500">
                    <p>Nenhum slider configurado para esta página.</p>
                    <a href="{{ route('admin.sliders.create', ['category_id' => request('category_id')]) }}" class="text-netflix hover:underline text-sm font-bold mt-2 inline-block">Adicionar primeiro destaque</a>
                </div>
            @endforelse
        </div>
    </div>

    {{-- BLOCO DE SEÇÕES --}}
    <div>
        <div class="flex items-center gap-2 mb-4">
            <i class="fa-solid fa-bars-staggered text-netflix text-xl"></i>
            <h2 class="text-xl font-bold text-white uppercase tracking-wider">Fileiras de Conteúdo (Seções)</h2>
        </div>
        
        <div id="sections-list" class="space-y-3">
            @forelse ($sections as $section)
                <div class="section-item bg-neutral-900 border border-neutral-800 rounded-xl p-5 flex items-center gap-4 cursor-grab active:cursor-grabbing hover:border-neutral-700 transition-all {{ !$section->is_active ? 'opacity-40 grayscale' : '' }}"
                     data-id="{{ $section->id }}">

                {{-- Drag handle --}}
                <div class="text-neutral-600 hover:text-white">
                    <i class="fa-solid fa-grip-vertical text-lg"></i>
                </div>

                {{-- Order badge --}}
                <div class="bg-neutral-800 w-8 h-8 rounded flex items-center justify-center text-xs font-bold text-neutral-400">
                    {{ $section->order + 1 }}
                </div>

                {{-- Info --}}
                <div class="flex-1">
                    <h3 class="font-bold text-white">{{ $section->title }}</h3>
                    <div class="flex items-center gap-2 mt-1">
                        @php
                            $typeColors = [
                                'custom' => 'bg-purple-600/20 text-purple-400',
                                'genre' => 'bg-blue-600/20 text-blue-400',
                                'trending' => 'bg-orange-600/20 text-orange-400',
                                'network' => 'bg-green-600/20 text-green-400',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs {{ $typeColors[$section->type] ?? '' }}">
                            {{ strtoupper($section->type) }}
                        </span>
                        <span class="text-xs text-neutral-500">{{ $section->content_type }}</span>

                        @if($section->type === 'genre' && $section->genre)
                            <span class="text-xs text-neutral-400">• {{ $section->genre->name }}</span>
                        @endif

                        @if($section->type === 'network' && $section->network)
                            <span class="text-xs text-neutral-400">• {{ $section->network->name }}</span>
                        @endif

                        @if($section->type === 'trending' && $section->trending_period)
                            <span class="text-xs text-neutral-400">• {{ $section->trending_period }}</span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    {{-- Toggle --}}
                    <form action="{{ route('admin.sections.toggle', $section->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-sm {{ $section->is_active ? 'text-green-400' : 'text-red-400' }}" title="{{ $section->is_active ? 'Desativar' : 'Ativar' }}">
                            <i class="fa-solid {{ $section->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                        </button>
                    </form>

                    @if($section->type === 'custom')
                        <a href="{{ route('admin.sections.items', $section->id) }}" class="text-purple-400 hover:text-purple-300" title="Gerenciar itens">
                            <i class="fa-solid fa-list-check"></i>
                        </a>
                    @endif

                    <a href="{{ route('admin.sections.edit', $section->id) }}" class="text-blue-500 hover:text-blue-400">
                        <i class="fa-solid fa-edit"></i>
                    </a>

                    <form action="{{ route('admin.sections.delete', $section->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="text-red-500 hover:text-red-400 swal-delete">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-neutral-900 rounded-lg p-8 text-center text-neutral-500">
                <i class="fa-solid fa-layer-group text-4xl mb-3"></i>
                <p>Nenhuma seção criada ainda.</p>
            </div>
        @endforelse
    </div>

    <x-swal />
</section>

{{-- SortableJS CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    const el = document.getElementById('sections-list');
    if (el) {
        Sortable.create(el, {
            animation: 200,
            ghostClass: 'opacity-30',
            handle: '.fa-grip-vertical',
            onEnd: function () {
                const ids = [...el.querySelectorAll('.section-item')].map(item => item.dataset.id);
                fetch('{{ route("admin.sections.reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ ids })
                });
            }
        });
    }
</script>
@endsection

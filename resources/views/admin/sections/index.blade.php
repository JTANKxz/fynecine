@extends('layouts.admin')

@section('title', 'Seções da Home')

@section('content')
<section>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Seções da Home e Categorias</h1>
            <p class="text-neutral-400 mt-1">Configure as linhas de conteúdo de cada página do aplicativo.</p>
        </div>
        <a href="{{ route('admin.sections.create') }}" 
           class="bg-netflix hover:bg-netflix/90 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-netflix/20 transition-all active:scale-95 flex items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            Nova Seção
        </a>
    </div>

    {{-- Filtros de Categoria --}}
    <div class="mb-8 flex items-center gap-3 overflow-x-auto pb-2">
        <a href="{{ route('admin.sections.index') }}" 
           class="px-5 py-2.5 rounded-full text-sm font-bold transition-all {{ !request('category_id') ? 'bg-netflix text-white shadow-lg shadow-netflix/20' : 'bg-neutral-800 text-neutral-400 hover:bg-neutral-700' }}">
            Home (Geral)
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('admin.sections.index', ['category_id' => $cat->id]) }}" 
               class="px-5 py-2.5 rounded-full text-sm font-bold transition-all {{ request('category_id') == $cat->id ? 'bg-netflix text-white shadow-lg shadow-netflix/20' : 'bg-neutral-800 text-neutral-400 hover:bg-neutral-700' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-900 border border-green-600 text-green-100 px-4 py-2 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div id="sections-list" class="space-y-2">
        @forelse ($sections as $section)
            <div class="section-item bg-neutral-900 border border-neutral-800 rounded-lg p-4 flex items-center gap-4 cursor-grab active:cursor-grabbing hover:border-neutral-700 transition {{ !$section->is_active ? 'opacity-50' : '' }}"
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

@extends('layouts.admin')

@section('title', 'Gerenciar Itens da Seção')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.adult.home-sections.index') }}" class="text-neutral-400 hover:text-white flex items-center gap-2 mb-2">
        <i class="fa-solid fa-arrow-left"></i> Voltar
    </a>
    <h2 class="text-2xl font-bold">Gerenciar Itens: {{ $section->title }}</h2>
    <p class="text-neutral-500">Seção do tipo: <span class="uppercase font-bold text-netflix">{{ $section->type }}</span></p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Adicionar Novo Item -->
    <div class="lg:col-span-1">
        <div class="bg-neutral-900 p-6 rounded-lg border border-neutral-800 sticky top-6">
            <h3 class="text-lg font-bold mb-4">Adicionar Item</h3>
            <form action="{{ route('admin.adult.home-sections.items.add', $section->id) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-neutral-400 mb-1">Tipo de Item</label>
                    <select name="item_type" id="item_type_selector" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix" onchange="toggleItemSelectors()">
                        <option value="gallery">Galeria</option>
                        <option value="media">Mídia Avulsa</option>
                        <option value="model">Modelo</option>
                        <option value="collection">Coleção</option>
                    </select>
                </div>

                <div id="selector_gallery" class="mb-4 item-selector">
                    <label class="block text-sm font-medium text-neutral-400 mb-1">Selecionar Galeria</label>
                    <select name="item_id_gallery" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
                        @foreach($galleries as $gallery)
                            <option value="{{ $gallery->id }}">{{ $gallery->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="selector_media" class="mb-4 item-selector hidden">
                    <label class="block text-sm font-medium text-neutral-400 mb-1">Selecionar Mídia Avulsa</label>
                    <select name="item_id_media" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
                        @foreach($media as $m)
                            <option value="{{ $m->id }}">{{ $m->title ?? $m->url }} ({{ $m->type }})</option>
                        @endforeach
                    </select>
                </div>

                <div id="selector_model" class="mb-4 item-selector hidden">
                    <label class="block text-sm font-medium text-neutral-400 mb-1">Selecionar Modelo</label>
                    <select name="item_id_model" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
                        @foreach($models as $model)
                            <option value="{{ $model->id }}">{{ $model->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="selector_collection" class="mb-4 item-selector hidden">
                    <label class="block text-sm font-medium text-neutral-400 mb-1">Selecionar Coleção</label>
                    <select name="item_id_collection" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
                        @foreach($collections as $collection)
                            <option value="{{ $collection->id }}">{{ $collection->title }}</option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="item_id" id="final_item_id">

                <div class="mb-6">
                    <label for="order" class="block text-sm font-medium text-neutral-400 mb-1">Ordem de Exibição</label>
                    <input type="number" name="order" value="0" class="w-full bg-neutral-800 border border-neutral-700 rounded px-3 py-2 text-white focus:outline-none focus:border-netflix">
                </div>

                <button type="submit" onclick="copyId()" class="w-full bg-netflix py-2 rounded font-bold hover:bg-netflix/80 transition">
                    Adicionar à Seção
                </button>
            </form>
        </div>
    </div>

    <!-- Lista de Itens -->
    <div class="lg:col-span-2">
        <div class="bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-neutral-950 border-b border-neutral-800 text-neutral-400 text-sm">
                        <th class="p-4 w-16">Ordem</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Item</th>
                        <th class="p-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800">
                    @forelse($items as $item)
                    @php $target = $item->target; @endphp
                    <tr class="hover:bg-neutral-800/50 transition">
                        <td class="p-4 text-neutral-500">{{ $item->order }}</td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-[10px] uppercase font-bold bg-neutral-800 text-neutral-400">
                                {{ $item->item_type }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($target)
                                <div class="font-bold">{{ $target->title ?? $target->name ?? 'Sem nome' }}</div>
                                <div class="text-[10px] text-neutral-500">{{ $target->slug ?? $target->id }}</div>
                            @else
                                <span class="text-red-500 italic">Item não encontrado (ID: {{ $item->item_id }})</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <form action="{{ route('admin.adult.home-sections.items.remove', $item->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-400" onclick="return confirm('Remover este item da seção?')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-10 text-center text-neutral-500 italic">Nenhum item adicionado manualmente.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function toggleItemSelectors() {
        const type = document.getElementById('item_type_selector').value;
        document.querySelectorAll('.item-selector').forEach(el => el.classList.add('hidden'));
        document.getElementById('selector_' + type).classList.remove('hidden');
    }

    function copyId() {
        const type = document.getElementById('item_type_selector').value;
        const selector = document.querySelector('select[name="item_id_' + type + '"]');
        document.getElementById('final_item_id').value = selector.value;
    }
</script>
@endsection

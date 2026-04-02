@extends('layouts.admin')

@section('title', 'Create Slider')

@section('content')
    <section>

        <h2 class="text-xl font-bold mb-4">Adicionar Item ao Slider</h2>

        <form action="{{ route('admin.sliders.store') }}" method="POST" class="bg-neutral-900 p-5 rounded space-y-4">

            @csrf

            <div class="grid md:grid-cols-4 gap-4">

                {{-- Buscar conteúdo --}}
                <div class="md:col-span-2">
                    <label class="block text-sm text-neutral-400 mb-1">
                        Buscar Filme ou Série
                    </label>

                    <input type="text" id="search" placeholder="Digite o nome do filme ou série..."
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                </div>

                {{-- Categoria --}}
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        Exibir em:
                    </label>
                    <select name="content_category_id" class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                        <option value="">Home (Geral)</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('content_category_id', request('category_id')) == $cat->id ? 'selected' : 'active' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- posição --}}
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        Posição
                    </label>

                    <input type="number" name="position" value="0"
                        class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                </div>

            </div>

            {{-- resultados da busca --}}
            <div id="results" class="bg-neutral-800 rounded hidden max-h-60 overflow-y-auto">
            </div>

            {{-- conteúdo selecionado --}}
            <input type="hidden" name="content_id" id="content_id">
            <input type="hidden" name="content_type" id="content_type">

            <div id="selected" class="hidden bg-neutral-800 p-4 rounded flex items-center gap-4">

                <img id="preview_backdrop" class="w-40 rounded">

                <div>
                    <h3 id="preview_title" class="font-bold text-lg"></h3>

                    <p class="text-sm text-neutral-400">
                        ⭐ <span id="preview_rating"></span>
                        • <span id="preview_year"></span>
                    </p>
                </div>

            </div>

            {{-- botões --}}
            <div class="flex gap-3">

                <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">
                    Salvar no Slider
                </button>

                <a href="{{ route('admin.sliders.index') }}"
                    class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">
                    Cancelar
                </a>

            </div>

        </form>

    </section>


    <script>
        const searchInput = document.getElementById('search');
        const resultsBox = document.getElementById('results');

        searchInput.addEventListener('keyup', function() {

            let query = this.value;

            if (query.length < 2) {
                resultsBox.classList.add('hidden');
                return;
            }

            fetch(`/dashzin/sliders/search?query=${query}`)
                .then(res => res.json())
                .then(data => {

                    resultsBox.innerHTML = '';
                    resultsBox.classList.remove('hidden');

                    data.forEach(item => {

                        let div = document.createElement('div');

                        div.className = "p-3 hover:bg-neutral-700 cursor-pointer flex justify-between";

                        div.innerHTML = `
                    <span>${item.title} (${item.year})</span>
                    <span class="text-neutral-400">${item.type}</span>
                `;

                        div.onclick = () => selectContent(item);

                        resultsBox.appendChild(div);

                    });

                });

        });

        function selectContent(item) {

            document.getElementById('content_id').value = item.id;
            document.getElementById('content_type').value = item.type;

            document.getElementById('preview_title').innerText = item.title;
            document.getElementById('preview_rating').innerText = item.rating;
            document.getElementById('preview_year').innerText = item.year;

            document.getElementById('preview_backdrop').src = item.backdrop;

            document.getElementById('selected').classList.remove('hidden');

            resultsBox.classList.add('hidden');
        }
    </script>

@endsection

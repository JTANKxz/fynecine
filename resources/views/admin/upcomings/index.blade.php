@extends('layouts.admin')

@section('title', 'Lançamentos Em Breve')

@section('content')
<section class="mb-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-clock text-blue-500"></i> Em Breve (Upcomings)
            </h2>
            <p class="text-xs text-neutral-500">Adicione conteúdos que ainda vão lançar para mostrar trailers no App.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-900 border border-green-600 text-green-100 px-4 py-2 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-900 p-5 rounded space-y-4 shadow-lg border border-neutral-800">
        <h3 class="font-bold text-lg mb-2">Adicionar Novo</h3>
        <div class="grid md:grid-cols-4 gap-3">
            <input id="search" class="p-2 bg-neutral-800 rounded outline-none border border-neutral-700" placeholder="Nome do filme/série">
            
            <select id="type" class="p-2 bg-neutral-800 rounded outline-none border border-neutral-700">
                <option value="movie">Filmes</option>
                <option value="tv">Séries</option>
            </select>

            <button onclick="searchUpcoming()" class="bg-netflix rounded p-2 text-white font-bold hover:bg-red-700 transition">
                <i class="fa-solid fa-search mr-2"></i>Buscar no TMDB
            </button>
            <button onclick="document.getElementById('search').value=''; document.getElementById('results').innerHTML=''" class="bg-neutral-700 rounded p-2 text-white font-bold hover:bg-neutral-600 transition">
                <i class="fa-solid fa-eraser mr-2"></i>Limpar
            </button>
        </div>

        <div id="results" class="grid grid-cols-2 md:grid-cols-6 gap-4 mt-5"></div>
        <div id="loading" class="hidden text-center py-10">
            <i class="fa-solid fa-spinner fa-spin text-4xl text-netflix"></i>
        </div>
    </div>
</section>

<section>
    <div class="bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800">
        <div class="p-4 bg-neutral-800 border-b border-neutral-700">
            <h3 class="font-bold">Conteúdos Registrados</h3>
        </div>
        <table class="w-full text-left">
            <thead class="bg-neutral-800 text-xs text-neutral-400">
                <tr>
                    <th class="p-4">Capa</th>
                    <th class="p-4">Título</th>
                    <th class="p-4">Lançamento</th>
                    <th class="p-4">Trailer</th>
                    <th class="p-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($upcomings as $upcoming)
                <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                    <td class="p-2">
                        <img src="{{ $upcoming->poster_path }}" class="w-10 rounded">
                    </td>
                    <td class="p-4 font-bold">
                        {{ $upcoming->title }}
                        <span class="ml-2 text-[10px] bg-neutral-700 px-2 py-0.5 rounded text-white">{{ strtoupper($upcoming->type) }}</span>
                    </td>
                    <td class="p-4 text-sm text-yellow-500 font-bold">
                        {{ $upcoming->release_date ? \Carbon\Carbon::parse($upcoming->release_date)->format('d/m/Y') : 'Em Breve' }}
                    </td>
                    <td class="p-4">
                        @if($upcoming->trailer_key)
                            <a href="https://youtube.com/watch?v={{ $upcoming->trailer_key }}" target="_blank" class="text-xs text-red-500 hover:underline">
                                <i class="fa-brands fa-youtube mr-1"></i>Ver Trailer
                            </a>
                        @else
                            <span class="text-xs text-neutral-600">Nenhum</span>
                        @endif
                    </td>
                    <td class="p-4">
                        <form action="{{ route('admin.upcomings.destroy', $upcoming->id) }}" method="POST" onsubmit="return confirm('Excluir este item?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-white"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-4 text-center text-sm text-neutral-500">Nenhum lançamento adicionado ainda.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-4">
            {{ $upcomings->links() }}
        </div>
    </div>
</section>

<script>
    async function searchUpcoming() {
        const query = document.getElementById("search").value;
        const type = document.getElementById("type").value;
        const results = document.getElementById("results");
        const loading = document.getElementById("loading");

        if(!query) return;

        results.innerHTML = "";
        loading.classList.remove("hidden");

        try {
            const params = new URLSearchParams({ query, type, target: 'upcoming', adult: false });
            const response = await fetch(`/dashzin/tmdb/search?${params}`);
            const data = await response.json();

            loading.classList.add("hidden");

            if (!response.ok) {
                results.innerHTML = `<p class="text-red-500 col-span-full">Erro na busca.</p>`;
                return;
            }

            const imageBase = "https://image.tmdb.org/t/p/w500";

            data.results.forEach(item => {
                const title = item.title || item.name;
                const poster = item.poster_path ? imageBase + item.poster_path : "https://via.placeholder.com/500x750?text=Sem+Imagem";
                
                const button = item.imported ?
                    `<button class="bg-green-600 w-full mt-2 text-xs p-1 rounded cursor-default font-bold">Na Lista</button>` :
                    `<button id="btn-upc-${item.id}" class="bg-blue-600 w-full mt-2 text-xs p-1 font-bold rounded hover:bg-blue-700 transition" onclick="importUpcoming(${item.id}, '${type}')">+ IMPORTAR</button>`;

                results.innerHTML += `
                <div class="bg-neutral-800 rounded p-2 shadow border border-neutral-700">
                    <img src="${poster}" class="rounded w-full aspect-[2/3] object-cover mb-2">
                    <p class="text-[10px] font-bold truncate">${title}</p>
                    ${button}
                </div>`;
            });

        } catch (error) {
            loading.classList.add("hidden");
            results.innerHTML = `<p class="text-red-500 col-span-full">Erro interno.</p>`;
        }
    }

    async function importUpcoming(id, type) {
        const btn = document.getElementById(`btn-upc-${id}`);
        if(btn) {
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i>`;
            btn.disabled = true;
        }

        try {
            const response = await fetch('/dashzin/upcomings/import', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ tmdb_id: id, type: type })
            });

            const data = await response.json();
            
            if(data.success) {
                if(btn) {
                    btn.innerHTML = 'Adicionado!';
                    btn.className = "bg-green-600 w-full mt-2 text-xs p-1 font-bold rounded cursor-default";
                }
                setTimeout(() => window.location.reload(), 1000); // recarrega a página para atualizar a tabela
            } else {
                if(btn) btn.innerHTML = 'Erro';
            }
        } catch (e) {
            if(btn) btn.innerHTML = 'Erro';
        }
    }
</script>
@endsection

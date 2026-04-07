@extends('layouts.admin')

@section('title', 'TMDB')

@section('content')
<!-- STATS -->
<!-- BUSCA TMDB COM FILTROS E IDIOMA PT-BR -->
<section>
    <h2 class="text-xl font-bold mb-4">Buscar no TMDB (pt-BR)</h2>
    <div class="bg-neutral-900 p-5 rounded space-y-4">
        <!-- Filtros Avançados -->
        <div class="grid md:grid-cols-5 gap-3">
            <input id="search" class="p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                placeholder="Nome do filme/série">

            <div class="relative">
                <input id="yearFrom"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="Ano inicial" type="number" min="1900" max="2025">
            </div>

            <div class="relative">
                <input id="yearTo"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="Ano final" type="number" min="1900" max="2025">
            </div>

            <select id="genre" class="p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <option value="">Todos os gêneros</option>
                <option value="28">Ação</option>
                <option value="12">Aventura</option>
                <option value="16">Animação</option>
                <option value="35">Comédia</option>
                <option value="80">Crime</option>
                <option value="99">Documentário</option>
                <option value="18">Drama</option>
                <option value="10751">Família</option>
                <option value="14">Fantasia</option>
                <option value="36">História</option>
                <option value="27">Terror</option>
                <option value="10402">Música</option>
                <option value="9648">Mistério</option>
                <option value="10749">Romance</option>
                <option value="878">Ficção científica</option>
                <option value="10770">Cinema TV</option>
                <option value="53">Thriller</option>
                <option value="10752">Guerra</option>
                <option value="37">Faroeste</option>
            </select>

            <select id="sortBy" class="p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <option value="popularity.desc">Mais populares</option>
                <option value="popularity.asc">Menos populares</option>
                <option value="vote_average.desc">Melhor avaliados</option>
                <option value="vote_average.asc">Pior avaliados</option>
                <option value="release_date.desc">Mais recentes</option>
                <option value="release_date.asc">Mais antigos</option>
            </select>
        </div>

        <div class="grid md:grid-cols-3 gap-3">
            <select id="type" class="p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <option value="movie">Filmes</option>
                <option value="tv">Séries</option>
            </select>

            <div class="flex items-center gap-6 p-2 bg-neutral-800 rounded-xl border border-neutral-700">
                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" id="modeAnime" class="sr-only peer">
                    <div class="w-11 h-6 bg-neutral-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-netflix"></div>
                    <span class="ml-3 text-sm font-bold text-neutral-400 group-hover:text-white transition-colors uppercase tracking-tighter">Modo Anime</span>
                </label>

                <label class="relative inline-flex items-center cursor-pointer group">
                    <input type="checkbox" id="modeDorama" class="sr-only peer">
                    <div class="w-11 h-6 bg-neutral-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    <span class="ml-3 text-sm font-bold text-neutral-400 group-hover:text-white transition-colors uppercase tracking-tighter">Modo Dorama</span>
                </label>
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="adult" class="rounded accent-netflix w-5 h-5">
                    <span class="text-sm">Adulto</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="importCast" class="rounded accent-netflix w-5 h-5" checked>
                    <span class="text-sm">Importar Elenco</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button onclick="searchTMDB()" class="bg-netflix rounded p-2 flex-1 hover:bg-red-700 transition">
                    <i class="fa-solid fa-search mr-2"></i>Buscar
                </button>
                <button onclick="clearFilters()"
                    class="bg-neutral-700 rounded p-2 px-4 hover:bg-neutral-600 transition">
                    <i class="fa-solid fa-eraser"></i>
                </button>
            </div>
        </div>

        <!-- Resultados com capas no formato correto -->
        <div id="results" class="grid grid-cols-2 md:grid-cols-6 gap-4 mt-5"></div>
        <div id="loading" class="hidden text-center py-10">
            <i class="fa-solid fa-spinner fa-spin text-4xl text-netflix"></i>
        </div>
    </div>
</section>
<script>
    let currentPage = 1;
    let selectedTMDB = null;

    async function searchTMDB(page = 1) {

        const query = document.getElementById("search").value;
        const type = document.getElementById("type").value;
        const yearFrom = document.getElementById("yearFrom").value;
        const yearTo = document.getElementById("yearTo").value;
        const genre = document.getElementById("genre").value;
        const sortBy = document.getElementById("sortBy").value;
        const adult = document.getElementById("adult").checked;

        const results = document.getElementById("results");
        const loading = document.getElementById("loading");

        if (page === 1) {
            results.innerHTML = "";
        }

        loading.classList.remove("hidden");

        try {

            const params = new URLSearchParams({
                query,
                type,
                yearFrom,
                yearTo,
                genre,
                sortBy,
                adult,
                page
            });

            const response = await fetch(`/dashzin/tmdb/search?${params}`);
            const data = await response.json();

            if (!response.ok) {
                loading.classList.add("hidden");
                results.innerHTML = `
                    <div class="col-span-full text-center p-5 bg-red-900/20 border border-red-900 rounded">
                        <p class="text-red-500 font-bold">Erro: ${data.error || 'Erro desconhecido'}</p>
                    </div>`;
                return;
            }

            loading.classList.add("hidden");

            const imageBase = "https://image.tmdb.org/t/p/w500";

            data.results.forEach(item => {

                const title = item.title || item.name;
                const date = item.release_date || item.first_air_date || "";
                const year = date ? date.substring(0, 4) : "";

                const poster = item.poster_path ?
                    imageBase + item.poster_path :
                    "https://via.placeholder.com/500x750?text=Sem+Imagem";

                const mediaType = item.title ? "Filme" : "Série";

                const importedBadge = item.imported ?
                    `<span class="text-green-500 text-xs">✔ Importado</span>` :
                    "";

                const button = item.imported ?
                    `<button class="bg-green-600 w-full mt-2 text-xs p-1 rounded cursor-default">Importado</button>` :
                    `<button
                        id="btn-import-${item.id}"
                        class="bg-netflix w-full mt-2 text-xs p-1 rounded hover:bg-red-700 transition"
                        onclick="handleImport(${item.id}, '${type}')"
                   >
                        Importar
                   </button>`;

                results.innerHTML += `
                <div class="bg-neutral-800 rounded overflow-hidden hover:scale-105 transition shadow">

                    <img src="${poster}" class="movie-poster">

                    <div class="p-2">

                        <p class="text-sm font-semibold truncate">${title}</p>

                        <p class="text-xs text-neutral-400">${year} • ${mediaType}</p>

                        ${importedBadge}

                        ${button}

                    </div>

                </div>
            `;
            });

            currentPage = data.page;

            showLoadMore(data.page, data.total_pages);

        } catch (error) {

            loading.classList.add("hidden");

            results.innerHTML += `
        <div class="col-span-full text-center text-red-500">
            Erro ao buscar dados
        </div>`;
        }
    }

    function clearFilters() {

        document.getElementById("search").value = "";
        document.getElementById("yearFrom").value = "";
        document.getElementById("yearTo").value = "";
        document.getElementById("genre").value = "";
        document.getElementById("sortBy").value = "popularity.desc";
        document.getElementById("type").value = "movie";
        document.getElementById("adult").checked = false;

        document.getElementById("results").innerHTML = "";
    }

    function handleImport(id, type) {

        if (type === "tv") {

            openImportModal(id);
            return;

        }

        importItem(id, type, "full");
    }

    async function importItem(id, type, mode = "full") {

        const button = document.getElementById(`btn-import-${id}`);

        if (button) {

            button.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Importando...`;
            button.classList.remove("bg-netflix");
            button.classList.add("bg-yellow-600");
            button.disabled = true;

        }

        try {
            const animeSwitch = document.getElementById("modeAnime");
            const doramaSwitch = document.getElementById("modeDorama");
            let categoryId = null;

            if (animeSwitch && animeSwitch.checked) {
                categoryId = 1;
            } else if (doramaSwitch && doramaSwitch.checked) {
                categoryId = 4;
            }

            const importCast = document.getElementById("importCast").checked;

            const response = await fetch('/dashzin/tmdb/import', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    tmdb_id: id,
                    type: type,
                    mode: mode,
                    category_id: categoryId,
                    import_cast: importCast
                })
            });

            const data = await response.json();

            if (data.success && button) {

                button.innerHTML = "✔ Importado";
                button.classList.remove("bg-yellow-600");
                button.classList.add("bg-green-600");

            }

        } catch (error) {

            if (button) {

                button.innerHTML = "Erro";
                button.classList.remove("bg-yellow-600");
                button.classList.add("bg-red-600");

            }

        }
    }

    function openImportModal(id) {

        selectedTMDB = id;

        const modal = document.getElementById("importModal");

        modal.classList.remove("hidden");
        modal.classList.add("flex");
    }

    function importDetails() {

        importItem(selectedTMDB, "tv", "details");

        closeModal();
    }

    function fullImport() {

        importItem(selectedTMDB, "tv", "full");

        closeModal();
    }

    function closeModal() {

        const modal = document.getElementById("importModal");

        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }

    function showLoadMore(page, totalPages) {

        const container = document.getElementById("results");

        let oldButton = document.getElementById("loadMore");

        if (oldButton) oldButton.remove();

        if (page < totalPages) {

            container.innerHTML += `
        <div id="loadMoreContainer" class="col-span-full text-center mt-6">

            <button 
                id="loadMore"
                onclick="loadMore()"
                class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition"
            >
                Carregar mais
            </button>

        </div>`;
        }
    }

    function loadMore() {
        searchTMDB(currentPage + 1);
    }

    // Mutex for Anime/Dorama modes
    document.addEventListener('DOMContentLoaded', () => {
        const modeAnime = document.getElementById('modeAnime');
        const modeDorama = document.getElementById('modeDorama');
        
        if (modeAnime && modeDorama) {
            modeAnime.addEventListener('change', () => {
                if (modeAnime.checked) modeDorama.checked = false;
            });
            modeDorama.addEventListener('change', () => {
                if (modeDorama.checked) modeAnime.checked = false;
            });
        }
    });
</script>
<div id="importModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50">

    <div class="bg-neutral-900 p-6 rounded w-[400px] space-y-4">

        <h3 class="text-lg font-bold">Importar Série</h3>

        <button onclick="importDetails()" class="w-full bg-blue-600 p-2 rounded hover:bg-blue-700">
            Importar apenas detalhes
        </button>

        <button onclick="fullImport()" class="w-full bg-netflix p-2 rounded hover:bg-red-700">
            Full Import (temporadas + episódios)
        </button>

        <button onclick="closeModal()" class="w-full bg-neutral-700 p-2 rounded">
            Cancelar
        </button>

    </div>

</div>
@endsection

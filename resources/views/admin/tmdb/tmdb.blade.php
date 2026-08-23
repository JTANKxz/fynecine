@extends('layouts.admin')

@section('title', 'TMDB')

@section('content')
<!-- STATS -->
<!-- BUSCA TMDB COM FILTROS E IDIOMA PT-BR -->
<section>
    <div class="mb-5 rounded-xl border border-neutral-800 bg-neutral-900 p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-xl font-bold">Importacao e atualizacao TMDB</h2>
                <p class="mt-1 text-sm text-neutral-400">Defina o elenco padrao e atualize metadados sem alterar links, categorias ou tags manuais.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end">
                <label class="block text-sm font-semibold text-neutral-300">Atores por conteudo
                    <input id="castLimit" type="number" min="1" max="30" value="{{ $castLimit }}" class="mt-1 block w-24 rounded bg-neutral-800 px-3 py-2 text-white outline-none ring-1 ring-neutral-700 focus:ring-netflix">
                </label>
                <button type="button" onclick="saveCastLimit()" class="rounded bg-neutral-700 px-4 py-2 text-sm font-bold hover:bg-neutral-600">Salvar padrao</button>
            </div>
        </div>
        <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <button type="button" onclick="startBatch('movie','cast')" class="rounded-lg bg-blue-700 px-4 py-3 text-left font-bold hover:bg-blue-600">Atualizar elenco de todos os filmes</button>
            <button type="button" onclick="startBatch('tv','cast')" class="rounded-lg bg-blue-700 px-4 py-3 text-left font-bold hover:bg-blue-600">Atualizar elenco de todas as series</button>
            <button type="button" onclick="startBatch('movie','keywords')" class="rounded-lg bg-purple-700 px-4 py-3 text-left font-bold hover:bg-purple-600">Atualizar palavras-chave dos filmes</button>
            <button type="button" onclick="startBatch('tv','keywords')" class="rounded-lg bg-purple-700 px-4 py-3 text-left font-bold hover:bg-purple-600">Atualizar palavras-chave das series</button>
            <button type="button" onclick="startBatch('movie','logos', true)" class="rounded-lg bg-emerald-700 px-4 py-3 text-left font-bold hover:bg-emerald-600">Buscar logos dos filmes sem logo</button>
            <button type="button" onclick="startBatch('tv','logos', true)" class="rounded-lg bg-emerald-700 px-4 py-3 text-left font-bold hover:bg-emerald-600">Buscar logos das series sem logo</button>
        </div>
        <div id="batchProgress" class="mt-4 hidden rounded-lg border border-neutral-700 bg-neutral-950 p-4">
            <div class="flex items-center justify-between text-sm"><span id="batchText">Preparando...</span><button type="button" onclick="cancelBatch()" class="text-red-400 hover:text-red-300">Cancelar</button></div>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-neutral-800"><div id="batchBar" class="h-full w-0 bg-netflix transition-all"></div></div>
            <p id="batchCurrent" class="mt-2 truncate text-xs text-neutral-500"></p>
        </div>
    </div>
    <div class="mb-5 rounded-xl border border-neutral-800 bg-neutral-900 p-4 sm:p-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-netflix/15 text-netflix"><i class="fa-solid fa-fire"></i></span>
                    <div>
                        <h2 class="text-lg font-bold">Radar TMDb</h2>
                        <p class="text-xs text-neutral-400">Acompanhe o hype e importe títulos que ainda não estão no catálogo.</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2 text-[11px] text-neutral-400">
                <i class="fa-solid fa-filter text-netflix"></i>
                Curadoria sem títulos de origem indiana
            </div>
        </div>

        <div id="radarTabs" class="mt-5 flex gap-2 overflow-x-auto pb-1 scrollbar-thin">
            <button type="button" data-radar="trending_movies" class="radar-tab shrink-0 rounded-lg bg-netflix px-3 py-2 text-xs font-bold text-white">Filmes em alta</button>
            <button type="button" data-radar="trending_series" class="radar-tab shrink-0 rounded-lg bg-neutral-800 px-3 py-2 text-xs font-bold text-neutral-300 hover:bg-neutral-700">Séries em alta</button>
            <button type="button" data-radar="popular_movies" class="radar-tab shrink-0 rounded-lg bg-neutral-800 px-3 py-2 text-xs font-bold text-neutral-300 hover:bg-neutral-700">Filmes populares</button>
            <button type="button" data-radar="now_playing" class="radar-tab shrink-0 rounded-lg bg-neutral-800 px-3 py-2 text-xs font-bold text-neutral-300 hover:bg-neutral-700">Em cartaz</button>
            <button type="button" data-radar="popular_series" class="radar-tab shrink-0 rounded-lg bg-neutral-800 px-3 py-2 text-xs font-bold text-neutral-300 hover:bg-neutral-700">Séries populares</button>
            <button type="button" data-radar="on_the_air" class="radar-tab shrink-0 rounded-lg bg-neutral-800 px-3 py-2 text-xs font-bold text-neutral-300 hover:bg-neutral-700">Em exibição</button>
            <button type="button" data-radar="upcoming_series" class="radar-tab shrink-0 rounded-lg bg-neutral-800 px-3 py-2 text-xs font-bold text-neutral-300 hover:bg-neutral-700">Próximas estreias</button>
        </div>

        <div class="mt-4 grid gap-3 border-t border-neutral-800 pt-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                <label for="radarProvider" class="shrink-0 text-xs font-bold text-neutral-300">Filtrar por streaming</label>
                <select id="radarProvider" class="w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-netflix sm:max-w-xs">
                    <option value="">Todos os streamings</option>
                </select>
                <label for="targetNetwork" class="shrink-0 text-xs font-bold text-neutral-300">Vincular à rede</label>
                <select id="targetNetwork" class="w-full rounded-lg border border-neutral-700 bg-neutral-950 px-3 py-2 text-xs text-white outline-none focus:ring-1 focus:ring-netflix sm:max-w-xs">
                    <option value="">Não vincular a uma rede</option>
                    @foreach($networks as $network)
                        <option value="{{ $network->id }}">{{ $network->name }}</option>
                    @endforeach
                </select>
                <span class="text-[10px] text-neutral-500">Disponibilidade no Brasil via TMDb / JustWatch.</span>
            </div>
            <label class="flex min-h-10 items-center gap-2 rounded-lg border border-neutral-700 bg-neutral-800 px-3 text-xs text-neutral-300 cursor-pointer hover:bg-neutral-700">
                <input type="checkbox" id="showImportedRadar" class="h-4 w-4 rounded accent-netflix">
                Mostrar já importados
            </label>
        </div>

        <div id="radarLoading" class="py-8 text-center text-sm text-neutral-400">
            <i class="fa-solid fa-spinner fa-spin mr-2 text-netflix"></i>Carregando radar...
        </div>
        <div id="radarResults" class="hidden grid-flow-col auto-cols-[145px] gap-3 overflow-x-auto pb-2 sm:auto-cols-[165px]"></div>
        <div id="radarEmpty" class="hidden rounded-lg border border-neutral-800 bg-neutral-950/50 p-5 text-center text-sm text-neutral-400"></div>
    </div>

    <h2 class="mb-4 text-xl font-bold">Buscar no TMDb (pt-BR)</h2>
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

        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.45fr)_minmax(0,1.25fr)_auto]">
            <select id="type" class="p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <option value="movie">Filmes</option>
                <option value="tv">Séries</option>
            </select>

            <div class="grid grid-cols-1 gap-2 rounded-xl border border-neutral-700 bg-neutral-800 p-2 sm:grid-cols-2">
                <label class="flex min-h-10 items-center gap-2 rounded-lg px-2 transition hover:bg-neutral-700/60 cursor-pointer group">
                    <input type="checkbox" id="modeAnime" class="h-4 w-4 rounded accent-netflix">
                    <span class="text-xs font-bold text-neutral-400 group-hover:text-white transition-colors uppercase">Modo Anime</span>
                </label>

                <label class="flex min-h-10 items-center gap-2 rounded-lg px-2 transition hover:bg-neutral-700/60 cursor-pointer group">
                    <input type="checkbox" id="modeDorama" class="h-4 w-4 rounded accent-purple-500">
                    <span class="text-xs font-bold text-neutral-400 group-hover:text-white transition-colors uppercase">Modo Dorama</span>
                </label>
            </div>

            <div class="grid grid-cols-1 gap-2 rounded-xl border border-neutral-700 bg-neutral-800 p-2 sm:grid-cols-2">
                <label class="flex min-h-10 items-center gap-2 rounded-lg px-2 hover:bg-neutral-700/60 cursor-pointer">
                    <input type="checkbox" id="adult" class="rounded accent-netflix w-5 h-5">
                    <span class="text-sm">Adulto</span>
                </label>
                <label class="flex min-h-10 items-center gap-2 rounded-lg px-2 hover:bg-neutral-700/60 cursor-pointer">
                    <input type="checkbox" id="importCast" class="rounded accent-netflix w-5 h-5" checked>
                    <span class="text-xs">Importar elenco</span>
                </label>
            </div>

            <div class="flex gap-2 lg:w-44">
                <button type="button" onclick="searchTMDB()" class="bg-netflix rounded p-2 flex-1 hover:bg-red-700 transition">
                    <i class="fa-solid fa-search mr-2"></i>Buscar
                </button>
                <button type="button" onclick="clearFilters()"
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
    let selectedImportButtonId = null;
    let radarCollection = 'trending_movies';
    let radarPage = 1;
    let radarTotalPages = 1;

    const escapeHtml = (value = '') => String(value).replace(/[&<>'"]/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    }[character]));

    async function loadRadar(collection = radarCollection, page = 1) {
        const loading = document.getElementById('radarLoading');
        const results = document.getElementById('radarResults');
        const empty = document.getElementById('radarEmpty');
        const provider = document.getElementById('radarProvider').value;
        const showImported = document.getElementById('showImportedRadar').checked;
        const append = page > 1;

        loading.classList.remove('hidden');
        if (!append) {
            results.classList.add('hidden');
            results.classList.remove('grid');
            empty.classList.add('hidden');
        }

        document.querySelectorAll('.radar-tab').forEach((tab) => {
            const active = tab.dataset.radar === collection;
            tab.classList.toggle('bg-netflix', active);
            tab.classList.toggle('text-white', active);
            tab.classList.toggle('bg-neutral-800', !active);
            tab.classList.toggle('text-neutral-300', !active);
        });

        try {
            const params = new URLSearchParams({ collection, page });
            if (provider) params.set('provider', provider);
            const response = await fetch(`/dashzin/tmdb/radar?${params}`);
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Não foi possível carregar o radar.');

            loading.classList.add('hidden');
            const items = (data.results || []).filter((item) => showImported || !item.imported);
            if (!items.length && !append) {
                empty.textContent = showImported
                    ? 'Nenhum título disponível nesta curadoria agora.'
                    : 'Todos os títulos desta página já foram importados. Marque “Mostrar já importados” para conferi-los.';
                empty.classList.remove('hidden');
                return;
            }

            const imageBase = 'https://image.tmdb.org/t/p/w342';
            const cards = items.map((item) => {
                const title = escapeHtml(item.title || item.name || 'Sem título');
                const date = item.release_date || item.first_air_date || '';
                const year = date ? date.substring(0, 4) : '—';
                const rating = Number(item.vote_average || 0).toFixed(1);
                const typeLabel = data.type === 'movie' ? 'Filme' : 'Série';
                const buttonId = `btn-radar-${collection}-${item.id}`;
                const targetNetworkId = document.getElementById('targetNetwork').value;
                const action = item.imported
                    ? (targetNetworkId
                        ? `<button id="${buttonId}" type="button" onclick="handleRadarImport(${item.id}, '${data.type}', '${buttonId}')" class="mt-2 w-full rounded bg-sky-600 px-2 py-1.5 text-[10px] font-bold text-white transition hover:bg-sky-500"><i class="fa-solid fa-link mr-1"></i>Vincular à rede</button>`
                        : '<span class="mt-2 block rounded bg-emerald-500/15 px-2 py-1 text-center text-[10px] font-bold text-emerald-400">Já importado</span>')
                    : `<button id="${buttonId}" type="button" onclick="handleRadarImport(${item.id}, '${data.type}', '${buttonId}')" class="mt-2 w-full rounded bg-netflix px-2 py-1.5 text-[10px] font-bold text-white transition hover:bg-red-700"><i class="fa-solid fa-plus mr-1"></i>Importar</button>`;

                return `
                    <article class="relative overflow-hidden rounded-xl border border-neutral-800 bg-neutral-950 shadow-sm transition hover:-translate-y-0.5 hover:border-neutral-600">
                        <img src="${imageBase}${item.poster_path}" alt="" loading="lazy" class="aspect-[2/3] w-full object-cover" onerror="this.closest('article').style.display='none'">
                        <span class="absolute left-2 top-2 rounded-md border border-white/15 bg-black/80 px-1.5 py-1 text-[9px] font-extrabold uppercase tracking-wide text-white backdrop-blur">${typeLabel}</span>
                        <div class="p-2.5">
                            <p class="truncate text-xs font-bold text-white" title="${title}">${title}</p>
                            <div class="mt-1 flex items-center justify-between text-[10px] text-neutral-400"><span>${year}</span><span class="text-amber-400"><i class="fa-solid fa-star mr-1"></i>${rating}</span></div>
                            ${action}
                        </div>
                    </article>`;
            }).join('');
            const oldLoadMore = document.getElementById('radarLoadMore');
            if (oldLoadMore) oldLoadMore.remove();
            if (append) {
                results.insertAdjacentHTML('beforeend', cards);
            } else {
                results.innerHTML = cards;
            }

            radarCollection = collection;
            radarPage = data.page || page;
            radarTotalPages = data.total_pages || 1;
            results.classList.remove('hidden');
            results.classList.add('grid');
            if (radarPage < radarTotalPages) {
                results.insertAdjacentHTML('beforeend', `
                    <div id="radarLoadMore" class="flex items-center justify-center">
                        <button type="button" onclick="loadMoreRadar()" class="rounded-lg border border-neutral-700 bg-neutral-800 px-4 py-2 text-xs font-bold text-white transition hover:border-netflix hover:bg-neutral-700">
                            <i class="fa-solid fa-plus mr-1"></i>Carregar mais
                        </button>
                    </div>`);
            }
        } catch (error) {
            loading.classList.add('hidden');
            if (!append) {
                empty.textContent = error.message || 'Erro ao carregar o radar.';
                empty.classList.remove('hidden');
            }
        }
    }

    function loadMoreRadar() {
        if (radarPage < radarTotalPages) loadRadar(radarCollection, radarPage + 1);
    }

    async function loadRadarProviders() {
        const select = document.getElementById('radarProvider');
        try {
            const response = await fetch('/dashzin/tmdb/radar/providers');
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Erro ao carregar provedores.');

            select.innerHTML = '<option value="">Todos os streamings</option>' + (data.providers || []).map((provider) =>
                `<option value="${provider.id}">${escapeHtml(provider.name)}</option>`
            ).join('');
        } catch (_) {
            select.innerHTML = '<option value="">Não foi possível carregar provedores</option>';
        }
    }

    function handleRadarImport(id, type, buttonId) {
        if (type === 'tv') {
            selectedImportButtonId = buttonId;
            openImportModal(id);
            return;
        }

        importItem(id, type, 'full', buttonId);
    }

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
                <div class="relative bg-neutral-800 rounded overflow-hidden hover:scale-105 transition shadow">

                    <img src="${poster}" class="movie-poster">
                    <span class="absolute left-2 top-2 rounded bg-black/80 px-1.5 py-1 text-[9px] font-extrabold uppercase tracking-wide text-white">${mediaType}</span>

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

            selectedImportButtonId = `btn-import-${id}`;
            openImportModal(id);
            return;

        }

        importItem(id, type, "full");
    }

    async function importItem(id, type, mode = "full", buttonId = `btn-import-${id}`) {

        const button = document.getElementById(buttonId);

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
            const networkId = document.getElementById("targetNetwork").value;

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
                    network_id: networkId || null,
                    import_cast: importCast,
                    cast_limit: Number(document.getElementById("castLimit").value)
                })
            });

            const data = await response.json();

            if (data.success && button) {

                const selectedNetwork = networkId
                    ? document.querySelector('#targetNetwork option:checked')?.textContent?.trim()
                    : null;
                button.innerHTML = selectedNetwork ? "✔ Vinculado" : "✔ Importado";
                if (selectedNetwork) button.title = `Importado e vinculado à rede ${selectedNetwork}`;
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

        importItem(selectedTMDB, "tv", "details", selectedImportButtonId || `btn-import-${selectedTMDB}`);

        closeModal();
    }

    function fullImport() {

        importItem(selectedTMDB, "tv", "full", selectedImportButtonId || `btn-import-${selectedTMDB}`);

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

        document.querySelectorAll('.radar-tab').forEach((tab) => {
            tab.addEventListener('click', () => loadRadar(tab.dataset.radar, 1));
        });
        document.getElementById('radarProvider').addEventListener('change', () => loadRadar(radarCollection, 1));
        document.getElementById('targetNetwork').addEventListener('change', () => loadRadar(radarCollection, 1));
        document.getElementById('showImportedRadar').addEventListener('change', () => loadRadar(radarCollection, 1));
        loadRadarProviders();
        loadRadar();
    });

    let batchCancelled = false;
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]').content;

    async function saveCastLimit() {
        const castLimit = Number(document.getElementById('castLimit').value);
        const response = await fetch('/dashzin/tmdb/cast-limit', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken()}, body: JSON.stringify({cast_limit: castLimit})});
        if (!response.ok) return alert('Nao foi possivel salvar o limite.');
        alert('Limite padrao salvo.');
    }

    function cancelBatch() { batchCancelled = true; }

    async function startBatch(type, action, missingLogoOnly = false) {
        const label = action === 'logos' ? 'Buscar clear logos para os títulos sem logo?' : 'Iniciar esta atualizacao? Mantenha esta pagina aberta ate terminar.';
        if (!confirm(label)) return;
        batchCancelled = false;
        const progress = document.getElementById('batchProgress');
        const text = document.getElementById('batchText');
        const current = document.getElementById('batchCurrent');
        const bar = document.getElementById('batchBar');
        progress.classList.remove('hidden'); bar.style.width = '0%'; text.textContent = 'Buscando conteudos...';
        const params = new URLSearchParams({type});
        if (missingLogoOnly) params.set('missing_logo', '1');
        const response = await fetch(`/dashzin/tmdb/batch-items?${params}`);
        const data = await response.json();
        const items = data.items || []; let completed = 0; let errors = 0;
        for (const item of items) {
            if (batchCancelled) break;
            current.textContent = item.title;
            try {
                const result = await fetch('/dashzin/tmdb/refresh-imported', {method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken()}, body: JSON.stringify({type, id: item.id, action, cast_limit: Number(document.getElementById('castLimit').value)})});
                if (!result.ok) errors++;
            } catch (_) { errors++; }
            completed++; const percent = items.length ? Math.round((completed / items.length) * 100) : 100;
            bar.style.width = `${percent}%`; text.textContent = `${completed}/${items.length} processados${errors ? ` - ${errors} erros` : ''}`;
        }
        current.textContent = batchCancelled ? 'Atualizacao cancelada.' : `Concluido. ${errors ? `${errors} erros.` : 'Sem erros.'}`;
    }
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

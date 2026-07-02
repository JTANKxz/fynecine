@extends('layouts.fyne')

@section('nav_catalogo_active', 'active')

@section('styles')
<style>
    /* ----- ESTILOS DA PÁGINA CATÁLOGO ----- */
    .page-title {
        padding: 20px 20px 10px;
        font-size: 28px;
        font-weight: 800;
        color: #f0f2f5;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
    }
    .page-title i {
        color: #7c3aed;
    }

    /* Abas de Categorias */
    .category-tabs {
        display: flex;
        gap: 12px;
        padding: 0 20px 10px;
        overflow-x: auto;
        scrollbar-width: none;
    }
    .category-tabs::-webkit-scrollbar {
        display: none;
    }
    .cat-tab {
        padding: 8px 16px;
        background: #1a1a1a;
        border: 1px solid #333;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 600;
        color: #b0b8c4;
        cursor: pointer;
        white-space: nowrap;
        transition: 0.2s;
    }
    .cat-tab.active {
        background: #7c3aed;
        color: #fff;
        border-color: #7c3aed;
    }

    /* Container de Filtros */
    .filters-container {
        padding: 10px 20px 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .filter-select {
        background: #0a0a0a;
        color: #f0f2f5;
        border: 1px solid #333;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 13px;
        outline: none;
        cursor: pointer;
    }
    .filter-select:focus {
        border-color: #7c3aed;
    }

    /* Grid de Catálogo */
    .grid-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 16px;
        padding: 0 20px 20px;
    }
    
    /* Card Styles for Grid (Override from scroll-horizontal) */
    .grid-container .card {
        width: 100%;
        margin-right: 0;
        aspect-ratio: 2/3;
        background: #1a1a1a;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        transition: transform 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .grid-container .card:hover {
        transform: scale(1.05);
        z-index: 10;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
    }
    .grid-container .card-img {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: 0.3s;
    }
    
    @media (max-width: 480px) {
        .grid-container {
            grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
            gap: 12px;
            padding: 0 16px 20px;
        }
    }

    /* Termo de busca */
    .search-term-alert {
        margin: 0 20px 10px;
        padding: 10px 15px;
        background: rgba(124, 58, 237, 0.1);
        border: 1px solid #7c3aed;
        border-radius: 8px;
        color: #f0f2f5;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .search-term-alert a {
        color: #a855f7;
        text-decoration: none;
        font-weight: 600;
    }
    .search-term-alert a:hover {
        text-decoration: underline;
    }
</style>
@endsection

@section('content')

    <div class="page-title">
        <i class="fas fa-compass"></i> Explorar
    </div>

    @if($query)
        <div class="search-term-alert">
            <span>Resultados para: <strong>{{ $query }}</strong></span>
            <a href="{{ route('frontend.search') }}">Limpar</a>
        </div>
    @endif

    <form id="filterForm" method="GET" action="{{ route('frontend.search') }}">
        @if($query)
            <input type="hidden" name="q" value="{{ $query }}">
        @endif
        
        <input type="hidden" name="categoria" id="inputCategoria" value="{{ $categoria }}">

        <div class="category-tabs">
            <div class="cat-tab {{ $categoria == 'todos' ? 'active' : '' }}" onclick="setCategoria('todos')">Todos</div>
            <div class="cat-tab {{ $categoria == 'filmes' ? 'active' : '' }}" onclick="setCategoria('filmes')">Filmes</div>
            <div class="cat-tab {{ $categoria == 'series' ? 'active' : '' }}" onclick="setCategoria('series')">Séries</div>
            <div class="cat-tab {{ $categoria == 'animes' ? 'active' : '' }}" onclick="setCategoria('animes')">Animes</div>
        </div>

        <div class="filters-container">
            <select name="ano" class="filter-select" id="filterAno" onchange="document.getElementById('filterForm').submit()">
                <option value="">Qualquer Ano</option>
                @for($y = date('Y'); $y >= 1990; $y--)
                    <option value="{{ $y }}" {{ $ano == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            
            <select name="avaliacao" class="filter-select" id="filterAvaliacao" onchange="document.getElementById('filterForm').submit()">
                <option value="">Qualquer Nota</option>
                <option value="9" {{ $avaliacao == '9' ? 'selected' : '' }}>Acima de 9.0</option>
                <option value="8" {{ $avaliacao == '8' ? 'selected' : '' }}>Acima de 8.0</option>
                <option value="7" {{ $avaliacao == '7' ? 'selected' : '' }}>Acima de 7.0</option>
                <option value="6" {{ $avaliacao == '6' ? 'selected' : '' }}>Acima de 6.0</option>
            </select>

            <select name="duracao" class="filter-select" id="filterDuracao" onchange="document.getElementById('filterForm').submit()">
                <option value="">Qualquer Duração</option>
                <option value="90" {{ $duracao == '90' ? 'selected' : '' }}>Até 1h30</option>
                <option value="120" {{ $duracao == '120' ? 'selected' : '' }}>Até 2h</option>
                <option value="150" {{ $duracao == '150' ? 'selected' : '' }}>Mais de 2h</option>
            </select>
        </div>
    </form>

    <div class="grid-container" id="gridCatalogo">
        @include('frontend.partials.catalog_cards', ['results' => $results])
    </div>

    <!-- Loading Spinner & Observer Target -->
    <div class="loading-spinner" id="loadingSpinner" style="display: {{ $hasMore ? 'block' : 'none' }}">
        <i class="fas fa-circle-notch"></i>
    </div>

@endsection

@section('scripts')
<script>
    function setCategoria(cat) {
        document.getElementById('inputCategoria').value = cat;
        document.getElementById('filterForm').submit();
    }

    // Lógica de Infinite Scroll
    let currentPage = 1;
    let isLoading = false;
    let hasMorePages = {{ $hasMore ? 'true' : 'false' }};
    
    const loadingSpinner = document.getElementById('loadingSpinner');
    const gridCatalogo = document.getElementById('gridCatalogo');

    // Observer para disparar o carregamento
    const observerOptions = {
        root: null,
        rootMargin: '0px 0px 200px 0px', // Pre-load 200px before reaching the end
        threshold: 0
    };

    const loadMoreObserver = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && hasMorePages && !isLoading) {
            loadMoreItems();
        }
    }, observerOptions);

    if (loadingSpinner) {
        loadMoreObserver.observe(loadingSpinner);
    }

    function loadMoreItems() {
        isLoading = true;
        currentPage++;
        
        const form = document.getElementById('filterForm');
        const urlParams = new URLSearchParams(new FormData(form));
        urlParams.append('page', currentPage);
        
        fetch(`{{ route('frontend.search') }}?${urlParams.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                // Insert the new HTML
                gridCatalogo.insertAdjacentHTML('beforeend', data.html);
                
                // Re-attach float card events for the newly added cards
                const newCards = gridCatalogo.querySelectorAll('.catalog-card:not(.event-attached)');
                if (typeof attachCardEvents === 'function') {
                    attachCardEvents(newCards);
                }
                newCards.forEach(card => card.classList.add('event-attached'));
            }
            
            hasMorePages = data.hasMore;
            if (!hasMorePages) {
                loadingSpinner.style.display = 'none';
                loadMoreObserver.disconnect();
            }
            
            isLoading = false;
        })
        .catch(error => {
            console.error('Error loading more items:', error);
            isLoading = false;
        });
    }

    // Attach marker to initial cards
    document.querySelectorAll('.catalog-card').forEach(card => card.classList.add('event-attached'));
</script>
@endsection

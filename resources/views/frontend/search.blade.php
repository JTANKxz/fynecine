@extends('layouts.fyne')

@section('title', 'FYNECINE - Catálogo')
@section('nav_explorar_active', 'active')

@section('styles')
<style>
    /* ----- ESTILOS DO CATÁLOGO ----- */
    .catalogo-container {
        padding: 20px 20px 20px;
    }

    /* Categorias (scroll lateral) */
    .categorias-section {
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        overflow-x: auto;
        padding-bottom: 8px;
        scroll-snap-type: x proximity;
        -webkit-overflow-scrolling: touch;
    }
    .categorias-section::-webkit-scrollbar {
        height: 4px;
    }
    .categorias-section::-webkit-scrollbar-thumb {
        background: #6b21a5;
        border-radius: 20px;
    }

    .categoria-btn {
        flex: 0 0 auto;
        padding: 8px 20px;
        border-radius: 30px;
        border: 1px solid rgba(124, 58, 237, 0.25);
        background: #0a0a0a;
        color: #b0b8c4;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: 0.2s;
        scroll-snap-align: start;
        white-space: nowrap;
    }
    .categoria-btn:hover {
        border-color: #7c3aed;
        color: #f0f2f5;
    }
    .categoria-btn.active {
        background: #7c3aed;
        border-color: #7c3aed;
        color: #fff;
    }

    /* Filtro (hambúrguer) */
    .filtro-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #0a0a0a;
        border: 1px solid rgba(124, 58, 237, 0.25);
        border-radius: 30px;
        padding: 6px 16px 6px 12px;
        color: #b0b8c4;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: 0.2s;
        flex-shrink: 0;
        margin-left: auto;
    }
    .filtro-toggle:hover {
        border-color: #7c3aed;
        color: #f0f2f5;
    }
    .filtro-toggle i {
        font-size: 18px;
    }

    /* Grid de cards */
    .catalogo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 20px 14px;
        padding-bottom: 10px;
    }

    @media (max-width: 600px) {
        .catalogo-grid {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 14px 10px;
        }
        .catalogo-container {
            padding: 10px 12px 16px;
        }
        .categoria-btn {
            font-size: 12px;
            padding: 6px 14px;
        }
        .filtro-toggle {
            font-size: 12px;
            padding: 4px 12px 4px 10px;
        }
        .filtro-toggle i {
            font-size: 16px;
        }
    }
    @media (min-width: 601px) {
        .catalogo-grid {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 24px 18px;
        }
    }
    @media (min-width: 1025px) {
        .catalogo-grid {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 28px 20px;
        }
    }

    /* ===== MENU DE FILTROS (lateral da direita) ===== */
    .filtro-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 999;
        justify-content: flex-end;
        animation: fadeOverlay 0.3s ease;
    }
    .filtro-overlay.active {
        display: flex;
    }
    @keyframes fadeOverlay { from { opacity: 0; } to { opacity: 1; } }

    .filtro-menu {
        background: #0a0a0a;
        width: 340px;
        max-width: 85vw;
        height: 100%;
        padding: 24px 20px 30px;
        box-shadow: -10px 0 40px rgba(0, 0, 0, 0.9);
        overflow-y: auto;
        transform: translateX(100%);
        transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        border-left: 1px solid #7c3aed;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .filtro-overlay.active .filtro-menu {
        transform: translateX(0);
    }
    @media (max-width: 600px) {
        .filtro-menu { width: 280px; padding: 20px 16px 24px; }
        .opcoes-scroll { max-height: 100px; }
    }

    .filtro-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 12px;
        border-bottom: 1px solid #1a1a1a;
        flex-shrink: 0;
    }
    .filtro-header h2 { font-size: 20px; font-weight: 700; color: #f0f2f5; }
    .filtro-header h2 i { color: #a855f7; margin-right: 8px; }
    .filtro-close {
        background: none;
        border: none;
        color: #b0b8c4;
        font-size: 24px;
        cursor: pointer;
        transition: 0.2s;
    }
    .filtro-close:hover { color: #a855f7; transform: rotate(90deg); }

    .filtro-grupo {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex-shrink: 0;
    }
    .filtro-grupo label {
        font-weight: 600;
        font-size: 14px;
        color: #b0b8c4;
        letter-spacing: 0.3px;
    }

    .opcoes-scroll {
        max-height: 140px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding-right: 4px;
    }
    .opcoes-scroll::-webkit-scrollbar { width: 4px; }
    .opcoes-scroll::-webkit-scrollbar-thumb { background: #6b21a5; border-radius: 20px; }
    .opcoes-scroll::-webkit-scrollbar-track { background: #0a0a0a; }

    .opcao {
        padding: 6px 14px;
        border-radius: 8px;
        border: 1px solid rgba(124, 58, 237, 0.15);
        background: transparent;
        color: #b0b8c4;
        font-size: 13px;
        cursor: pointer;
        transition: 0.2s;
        text-align: left;
        width: 100%;
    }
    .opcao:hover { border-color: #7c3aed; color: #f0f2f5; }
    .opcao.active { background: #7c3aed; border-color: #7c3aed; color: #fff; }

    .filtro-actions {
        display: flex;
        gap: 12px;
        margin-top: 10px;
        border-top: 1px solid #1a1a1a;
        padding-top: 16px;
        flex-shrink: 0;
    }
    .filtro-actions button {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 30px;
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        transition: 0.2s;
    }
    .btn-aplicar { background: #7c3aed; color: #fff; }
    .btn-aplicar:hover { background: #a855f7; }
    .btn-limpar { background: rgba(255, 255, 255, 0.05); color: #b0b8c4; border: 1px solid rgba(124, 58, 237, 0.2) !important; }
    .btn-limpar:hover { background: rgba(124, 58, 237, 0.15); border-color: #7c3aed !important; }

    /* ===== PAGINAÇÃO ===== */
    .pagination-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 40px;
        flex-wrap: wrap;
    }
    .btn-page {
        background: rgba(124, 58, 237, 0.1);
        color: #f0f2f5;
        border: 1px solid rgba(124, 58, 237, 0.3);
        border-radius: 8px;
        width: 40px;
        height: 40px;
        display: flex;
        justify-content: center;
        align-items: center;
        text-decoration: none;
        font-weight: 600;
        transition: 0.3s ease;
        font-size: 14px;
    }
    .btn-page:hover {
        background: rgba(124, 58, 237, 0.3);
        border-color: #7c3aed;
        transform: translateY(-2px);
    }
    .btn-page.active {
        background: #7c3aed;
        color: #fff;
        border-color: #7c3aed;
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
    }
    .btn-page.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }


    /* Estilo de Card adaptado da home */
    .catalogo-grid .card {
        border-radius: 12px;
        overflow: hidden;
        background: #0a0a0a;
        transition: transform 0.25s, box-shadow 0.25s;
        cursor: pointer;
        position: relative;
        aspect-ratio: 2 / 3;
        border: 1px solid rgba(124, 58, 237, 0.15);
        width: 100%;
    }
    .catalogo-grid .card:hover {
        transform: scale(1.02);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.8);
        border-color: #7c3aed;
    }
    .catalogo-grid .card-img {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.3s ease;
    }
    .catalogo-grid .card:hover .card-img {
        transform: scale(1.04);
    }
    .card-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        color: #a855f7;
        border: 1px solid rgba(124, 58, 237, 0.2);
        pointer-events: none;
    }

    .no-results {
        grid-column: 1 / -1;
        text-align: center;
        padding: 40px 0;
        color: #6b7385;
        font-size: 18px;
    }
    .no-results i {
        font-size: 40px;
        display: block;
        margin-bottom: 12px;
        color: #7c3aed;
    }

    .search-term-alert {
        margin-bottom: 16px;
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

    <!-- ===== CATÁLOGO ===== -->
    <div class="catalogo-container">

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
            <input type="hidden" name="genero" id="inputGenero" value="{{ $genero }}">
            <input type="hidden" name="ano" id="inputAno" value="{{ $ano }}">
            <input type="hidden" name="avaliacao" id="inputAvaliacao" value="{{ $avaliacao }}">
            <input type="hidden" name="duracao" id="inputDuracao" value="{{ $duracao }}">
        </form>

        <!-- Categorias (scroll lateral) + Filtro -->
        <div class="categorias-section" id="categoriasContainer">
            <button class="categoria-btn {{ $categoria == 'todos' ? 'active' : '' }}" onclick="setCategoria('todos')">Todos</button>
            <button class="categoria-btn {{ $categoria == 'filmes' ? 'active' : '' }}" onclick="setCategoria('filmes')">Filmes</button>
            <button class="categoria-btn {{ $categoria == 'series' ? 'active' : '' }}" onclick="setCategoria('series')">Séries</button>
            <button class="categoria-btn {{ $categoria == 'animes' ? 'active' : '' }}" onclick="setCategoria('animes')">Animes</button>
            
            <div class="filtro-toggle" id="filtroToggle">
                <i class="fas fa-sliders-h"></i> Filtros
            </div>
        </div>

        <!-- Grid de cards -->
        <div class="catalogo-grid" id="catalogoGrid">
            @include('frontend.partials.catalog_cards', ['results' => $results])
        </div>

        <!-- Paginação -->
        @if ($results->lastPage() > 1)
        <div class="pagination-container">
            @if ($results->onFirstPage())
                <span class="btn-page disabled"><i class="fas fa-chevron-left"></i></span>
            @else
                <a href="{{ $results->previousPageUrl() }}" class="btn-page"><i class="fas fa-chevron-left"></i></a>
            @endif
            
            @foreach ($results->getUrlRange(max(1, $results->currentPage() - 2), min($results->lastPage(), $results->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" class="btn-page {{ $page == $results->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if ($results->hasMorePages())
                <a href="{{ $results->nextPageUrl() }}" class="btn-page"><i class="fas fa-chevron-right"></i></a>
            @else
                <span class="btn-page disabled"><i class="fas fa-chevron-right"></i></span>
            @endif
        </div>
        @endif

    </div>

    <!-- ===== MENU DE FILTROS (lateral) ===== -->
    <div class="filtro-overlay" id="filtroOverlay">
        <div class="filtro-menu">
            <div class="filtro-header">
                <h2><i class="fas fa-sliders-h"></i> Filtros</h2>
                <button class="filtro-close" id="filtroClose"><i class="fas fa-times"></i></button>
            </div>

            <!-- Gênero -->
            <div class="filtro-grupo">
                <label>Gênero</label>
                <div class="opcoes-scroll" id="filtroGenero">
                    <div class="opcao {{ $genero == 'todos' || empty($genero) ? 'active' : '' }}" data-val="todos">Todos</div>
                    <div class="opcao {{ $genero == 'acao' ? 'active' : '' }}" data-val="acao">Ação</div>
                    <div class="opcao {{ $genero == 'aventura' ? 'active' : '' }}" data-val="aventura">Aventura</div>
                    <div class="opcao {{ $genero == 'comedia' ? 'active' : '' }}" data-val="comedia">Comédia</div>
                    <div class="opcao {{ $genero == 'drama' ? 'active' : '' }}" data-val="drama">Drama</div>
                    <div class="opcao {{ $genero == 'fantasia' ? 'active' : '' }}" data-val="fantasia">Fantasia</div>
                    <div class="opcao {{ $genero == 'ficcao' ? 'active' : '' }}" data-val="ficcao">Ficção Científica</div>
                    <div class="opcao {{ $genero == 'suspense' ? 'active' : '' }}" data-val="suspense">Suspense</div>
                    <div class="opcao {{ $genero == 'terror' ? 'active' : '' }}" data-val="terror">Terror</div>
                </div>
            </div>

            <!-- Ano -->
            <div class="filtro-grupo">
                <label>Ano</label>
                <div class="opcoes-scroll" id="filtroAno">
                    <div class="opcao {{ $ano == 'todos' || empty($ano) ? 'active' : '' }}" data-val="todos">Todos</div>
                    @for($y = date('Y'); $y >= 1990; $y--)
                        <div class="opcao {{ $ano == $y ? 'active' : '' }}" data-val="{{ $y }}">{{ $y }}</div>
                    @endfor
                </div>
            </div>

            <!-- Avaliação -->
            <div class="filtro-grupo">
                <label>Avaliação (mínima)</label>
                <div class="opcoes-scroll" id="filtroNota">
                    <div class="opcao {{ $avaliacao == '0' || empty($avaliacao) ? 'active' : '' }}" data-val="0">Todas</div>
                    <div class="opcao {{ $avaliacao == '9' ? 'active' : '' }}" data-val="9">9+ ⭐</div>
                    <div class="opcao {{ $avaliacao == '8' ? 'active' : '' }}" data-val="8">8+ ⭐</div>
                    <div class="opcao {{ $avaliacao == '7' ? 'active' : '' }}" data-val="7">7+ ⭐</div>
                    <div class="opcao {{ $avaliacao == '6' ? 'active' : '' }}" data-val="6">6+ ⭐</div>
                    <div class="opcao {{ $avaliacao == '5' ? 'active' : '' }}" data-val="5">5+ ⭐</div>
                </div>
            </div>

            <!-- Duração (minutos) -->
            <div class="filtro-grupo">
                <label>Duração (minutos)</label>
                <div class="opcoes-scroll" id="filtroDuracao">
                    <div class="opcao {{ $duracao == '0' || empty($duracao) ? 'active' : '' }}" data-val="0">Todas</div>
                    <div class="opcao {{ $duracao == '30' ? 'active' : '' }}" data-val="30">Até 30 min</div>
                    <div class="opcao {{ $duracao == '60' ? 'active' : '' }}" data-val="60">Até 60 min</div>
                    <div class="opcao {{ $duracao == '90' ? 'active' : '' }}" data-val="90">Até 90 min</div>
                    <div class="opcao {{ $duracao == '120' ? 'active' : '' }}" data-val="120">Até 120 min</div>
                    <div class="opcao {{ $duracao == '180' ? 'active' : '' }}" data-val="180">Até 180 min</div>
                </div>
            </div>

            <div class="filtro-actions">
                <button class="btn-aplicar" id="aplicarFiltros">Aplicar</button>
                <button class="btn-limpar" id="limparFiltros">Limpar</button>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    // ===== LÓGICA DO FORMULÁRIO =====
    function setCategoria(cat) {
        document.getElementById('inputCategoria').value = cat;
        document.getElementById('filterForm').submit();
    }

    // ===== MENU DE FILTROS =====
    const filtroOverlay = document.getElementById('filtroOverlay');
    const filtroToggle = document.getElementById('filtroToggle');
    const filtroClose = document.getElementById('filtroClose');

    function toggleFiltros(open) {
        if (open === undefined) {
            filtroOverlay.classList.toggle('active');
        } else if (open) {
            filtroOverlay.classList.add('active');
        } else {
            filtroOverlay.classList.remove('active');
        }
        document.body.style.overflow = filtroOverlay.classList.contains('active') ? 'hidden' : '';
    }

    filtroToggle.addEventListener('click', () => toggleFiltros(true));
    filtroClose.addEventListener('click', () => toggleFiltros(false));
    filtroOverlay.addEventListener('click', (e) => {
        if (e.target === filtroOverlay) toggleFiltros(false);
    });

    // Seleção de opções
    function setupFilterGroup(containerId, inputId) {
        const container = document.getElementById(containerId);
        container.querySelectorAll('.opcao').forEach(el => {
            el.addEventListener('click', () => {
                container.querySelectorAll('.opcao').forEach(o => o.classList.remove('active'));
                el.classList.add('active');
                document.getElementById(inputId).value = el.dataset.val;
            });
        });
    }

    // Setup inicial dos filtros
    setupFilterGroup('filtroGenero', 'inputGenero');
    setupFilterGroup('filtroAno', 'inputAno');
    setupFilterGroup('filtroNota', 'inputAvaliacao');
    setupFilterGroup('filtroDuracao', 'inputDuracao');

    document.getElementById('limparFiltros').addEventListener('click', () => {
        document.getElementById('inputGenero').value = 'todos';
        document.getElementById('inputAno').value = 'todos';
        document.getElementById('inputAvaliacao').value = '0';
        document.getElementById('inputDuracao').value = '0';
        document.getElementById('filterForm').submit();
    });

    document.getElementById('aplicarFiltros').addEventListener('click', () => {
        document.getElementById('filterForm').submit();
    });

    // Attach marker to initial cards
    document.querySelectorAll('.catalog-card').forEach(card => card.classList.add('event-attached'));
</script>
@endsection

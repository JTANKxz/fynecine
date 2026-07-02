@extends('layouts.fyne')

@section('title', 'FYNECINE - ' . $serie->name)

@section('styles')
<style>
    /* ----- HEADER OVERRIDE ----- */
    .header {
        position: fixed !important;
        background: linear-gradient(180deg, rgba(0,0,0,0.85) 0%, transparent 100%) !important;
    }

    /* ----- DETAILS BACKDROP (fixo com efeito de scroll) ----- */
    .details-backdrop {
        position: sticky;
        top: 0;
        left: 0;
        right: 0;
        width: 100%;
        height: 60vh;
        min-height: 400px;
        max-height: 600px;
        background-size: cover;
        background-position: center 30%;
        display: flex;
        align-items: flex-end;
        padding: 30px 24px 0;
        z-index: 10;
        border-bottom: 2px solid #7c3aed;
    }
    .details-backdrop::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(0deg, #000000 0%, rgba(0,0,0,0.4) 60%, transparent 100%);
        pointer-events: none;
    }

    /* ----- CONTEÚDO SOBREPOSTO (rola por cima) - LARGURA TOTAL ----- */
    .details-content-wrapper {
        position: relative;
        z-index: 20;
        margin-top: -20vh;
        padding: 0 24px 30px;
        background: linear-gradient(180deg, transparent 0%, #000000 10%);
        width: 100%;
        max-width: 100%;
    }

    .details-content {
        width: 100%;
        max-width: 100%;
    }

    .details-content .badge {
        display: inline-block;
        background: #7c3aed;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    .details-content h1 {
        font-size: 32px;
        font-weight: 700;
        line-height: 1.1;
        margin-bottom: 4px;
    }
    .details-content .meta {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 14px;
        color: #b0b8c4;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }
    .details-content .meta .avaliacao {
        display: flex;
        align-items: center;
        gap: 4px;
        color: #fbbf24;
    }
    .details-content .sinopse {
        font-size: 15px;
        line-height: 1.6;
        color: #cbd5e0;
        margin-bottom: 12px;
    }
    .details-content .classificacao {
        font-size: 14px;
        color: #b0b8c4;
        margin-bottom: 16px;
    }
    .details-content .classificacao span {
        color: #8a94a6;
        font-weight: 500;
    }

    /* ----- BOTÃO ASSISTIR (full width em mobile) ----- */
    .btn-assistir-full {
        background: #7c3aed;
        color: #fff;
        border: none;
        padding: 12px 24px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: auto;
        margin: 0 0 12px 0;
    }
    .btn-assistir-full:hover {
        background: #a855f7;
    }

    /* ----- AÇÕES COM ÍCONES ----- */
    .action-icons {
        display: flex;
        gap: 24px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .action-icons button {
        background: none;
        border: none;
        color: #b0b8c4;
        font-size: 13px;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
    }
    .action-icons button i {
        font-size: 22px;
    }
    .action-icons button:hover {
        color: #a855f7;
    }
    .action-icons button .label {
        font-size: 10px;
        color: #8a94a6;
    }

    /* ----- ELENCO ----- */
    .details-elenco {
        padding: 0 0 20px 0;
    }
    .details-elenco h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #f0f2f5;
    }
    .details-elenco h3 i {
        color: #a855f7;
        margin-right: 8px;
    }
    .elenco-list {
        display: flex;
        gap: 16px;
        overflow-x: auto;
        padding-bottom: 8px;
    }
    .elenco-list::-webkit-scrollbar {
        height: 4px;
    }
    .elenco-list::-webkit-scrollbar-thumb {
        background: #6b21a5;
        border-radius: 20px;
    }
    .elenco-item {
        flex: 0 0 80px;
        text-align: center;
    }
    .elenco-item .foto {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-size: cover;
        background-position: center;
        margin-bottom: 6px;
        border: 2px solid #7c3aed;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 20px;
        color: #a855f7;
        background-color: #2a2a2a;
    }
    .elenco-item .nome {
        font-size: 12px;
        color: #b0b8c4;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ----- TEMPORADAS (scroll lateral) ----- */
    .temporadas-section {
        padding: 0 0 20px 0;
    }
    .temporadas-section h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #f0f2f5;
    }
    .temporadas-section h3 i {
        color: #a855f7;
        margin-right: 8px;
    }
    .temporadas-scroll {
        display: flex;
        gap: 10px;
        overflow-x: auto;
        padding-bottom: 8px;
        scroll-snap-type: x proximity;
        -webkit-overflow-scrolling: touch;
    }
    .temporadas-scroll::-webkit-scrollbar {
        height: 4px;
    }
    .temporadas-scroll::-webkit-scrollbar-thumb {
        background: #6b21a5;
        border-radius: 20px;
    }

    .temp-btn {
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
    .temp-btn:hover {
        border-color: #7c3aed;
        color: #f0f2f5;
    }
    .temp-btn.active {
        background: #7c3aed;
        border-color: #7c3aed;
        color: #fff;
    }

    /* ----- EPISÓDIOS (cards com scroll lateral) ----- */
    .episodios-section {
        padding: 0 0 20px 0;
    }
    .episodios-section h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #f0f2f5;
    }
    .episodios-section h3 i {
        color: #a855f7;
        margin-right: 8px;
    }
    .episodios-scroll {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 8px;
        scroll-snap-type: x proximity;
        -webkit-overflow-scrolling: touch;
    }
    .episodios-scroll::-webkit-scrollbar {
        height: 4px;
    }
    .episodios-scroll::-webkit-scrollbar-thumb {
        background: #6b21a5;
        border-radius: 20px;
    }

    /* Card de episódio - hover apenas na imagem */
    .ep-card-wrapper {
        flex: 0 0 160px;
        scroll-snap-align: start;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }
    .ep-card-wrapper:hover .ep-card {
        border-color: #7c3aed;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.8);
    }

    .ep-card {
        border-radius: 12px;
        overflow: hidden;
        background: #0a0a0a;
        position: relative;
        aspect-ratio: 16 / 9;
        border: 1px solid rgba(124, 58, 237, 0.15);
        transition: border-color 0.3s, box-shadow 0.3s;
        width: 100%;
    }
    .ep-card-img {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.3s ease;
    }
    .ep-card-wrapper:hover .ep-card-img {
        transform: scale(1.05);
    }

    /* Duração no canto inferior direito do card */
    .ep-duration {
        position: absolute;
        bottom: 6px;
        right: 8px;
        background: rgba(0, 0, 0, 0.75);
        backdrop-filter: blur(4px);
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        color: #cbd5e0;
        border: 1px solid rgba(255,255,255,0.08);
        pointer-events: none;
    }

    /* Título do episódio fora do card, alinhado à esquerda */
    .ep-title {
        font-size: 13px;
        font-weight: 600;
        color: #f0f2f5;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        text-align: left;
        padding: 0 2px;
        min-width: 0;
    }

    /* ----- RELACIONADOS (mesmo estilo da home) ----- */
    .relacionados {
        padding: 0 0 10px 0;
    }
    .relacionados h3 {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #f0f2f5;
    }
    .relacionados h3 i {
        color: #a855f7;
        margin-right: 8px;
    }
    .scroll-horizontal {
        display: flex;
        gap: 14px;
        overflow-x: auto;
        padding-bottom: 8px;
        scroll-snap-type: x proximity;
        -webkit-overflow-scrolling: touch;
    }
    .scroll-horizontal::-webkit-scrollbar {
        height: 4px;
    }
    .scroll-horizontal::-webkit-scrollbar-thumb {
        background: #6b21a5;
        border-radius: 20px;
    }

    .card-wrapper {
        width: 140px;
        flex-shrink: 0;
        scroll-snap-align: start;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 6px;
        transition: transform 0.25s;
        min-width: 0;
    }
    .card-wrapper:hover {
        transform: scale(1.02);
    }
    .card {
        border-radius: 12px;
        overflow: hidden;
        background: #0a0a0a;
        position: relative;
        aspect-ratio: 2 / 3;
        border: 1px solid rgba(124, 58, 237, 0.15);
        transition: border-color 0.3s, box-shadow 0.3s;
        width: 100%;
    }
    .card-wrapper:hover .card {
        border-color: #7c3aed;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.8);
    }
    .card-img {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: transform 0.3s ease;
    }
    .card-wrapper:hover .card-img {
        transform: scale(1.04);
    }
    .card-title {
        font-size: 13px;
        font-weight: 600;
        color: #f0f2f5;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
        text-align: left;
        padding: 0 2px;
        min-width: 0;
    }

    /* ===== MODAL DE SERVIDORES ===== */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        padding: 20px;
        animation: fadeInModal 0.3s ease;
    }
    .modal-overlay.active {
        display: flex;
    }

    @keyframes fadeInModal {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }

    .modal-container {
        background: #0a0a0a;
        border: 1px solid #7c3aed;
        border-radius: 20px;
        max-width: 500px;
        width: 100%;
        max-height: 80vh;
        overflow-y: auto;
        padding: 24px 20px 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.9);
        position: relative;
    }
    .modal-container::-webkit-scrollbar { width: 4px; }
    .modal-container::-webkit-scrollbar-thumb { background: #6b21a5; border-radius: 20px; }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #1a1a1a;
    }
    .modal-header h2 { font-size: 20px; font-weight: 700; color: #f0f2f5; }
    .modal-header h2 i { color: #a855f7; margin-right: 8px; }
    .modal-close {
        background: none;
        border: none;
        color: #b0b8c4;
        font-size: 24px;
        cursor: pointer;
        transition: 0.2s;
        padding: 4px 8px;
    }
    .modal-close:hover { color: #a855f7; transform: rotate(90deg); }

    .server-item {
        background: #141414;
        border: 1px solid #2a2a2a;
        border-radius: 12px;
        padding: 12px 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        cursor: pointer;
        transition: 0.2s;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 10px;
    }
    .server-item:hover {
        border-color: #7c3aed;
        background: #1a1a1a;
        transform: translateX(4px);
    }
    .server-item .server-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        flex: 1;
        min-width: 120px;
    }
    .server-item .server-name { font-weight: 700; font-size: 15px; color: #f0f2f5; }
    .server-item .server-action {
        color: #a855f7;
        font-size: 18px;
        transition: 0.2s;
        padding: 4px 8px;
        border-radius: 30px;
        background: rgba(124, 58, 237, 0.1);
        border: 1px solid transparent;
    }
    .server-item:hover .server-action {
        background: rgba(124, 58, 237, 0.2);
        border-color: #7c3aed;
    }

    /* Responsividade geral */
    @media (max-width: 600px) {
        .details-backdrop {
            height: 50vh;
            min-height: 280px;
            max-height: 400px;
            padding: 16px 16px 0;
        }
        .details-content-wrapper { margin-top: -15vh; padding: 0 16px 20px; }
        .details-content h1 { font-size: 20px; margin-bottom: 2px; }
        .details-content .meta { font-size: 11px; gap: 8px; margin-bottom: 6px; }
        .details-content .sinopse { font-size: 13px; line-height: 1.4; margin-bottom: 8px; }
        .details-content .classificacao { font-size: 12px; margin-bottom: 12px; }
        .details-content .badge { font-size: 10px; padding: 3px 10px; margin-bottom: 4px; }
        .btn-assistir-full { width: 100%; border-radius: 30px; padding: 12px; font-size: 14px; margin: 0 0 10px 0; }
        .action-icons { gap: 10px; justify-content: space-around; }
        .action-icons button { font-size: 10px; }
        .action-icons button i { font-size: 18px; }
        .elenco-item { flex: 0 0 56px; }
        .elenco-item .foto { width: 56px; height: 56px; font-size: 14px; }
        .elenco-item .nome { font-size: 9px; }
        .ep-card-wrapper { flex: 0 0 130px; }
        .ep-title { font-size: 11px; }
        .ep-duration { font-size: 10px; padding: 1px 8px; }
        .card-wrapper { width: 100px; gap: 4px; }
        .card-title { font-size: 11px; }
        .scroll-horizontal, .episodios-scroll, .temporadas-scroll { gap: 8px; padding-bottom: 4px; }
        .relacionados, .episodios-section, .temporadas-section { padding: 0 0 12px 0; }
        .details-elenco { padding: 0 0 12px 0; }
        .temp-btn { font-size: 12px; padding: 6px 14px; }
    }

    @media (min-width: 601px) {
        .action-icons { gap: 28px; }
        .btn-assistir-full { width: auto; min-width: 200px; }
        .details-content-wrapper { padding-left: 40px; padding-right: 40px; }
        .details-content h1 { font-size: 38px; }
        .details-content .sinopse { font-size: 16px; max-width: 800px; }
        .ep-card-wrapper { flex: 0 0 200px; }
        .card-wrapper { width: 180px; }
    }

    @media (min-width: 1025px) {
        .details-backdrop { max-height: 600px; }
        .details-content h1 { font-size: 46px; }
        .details-content .sinopse { font-size: 17px; max-width: 900px; }
        .details-content-wrapper { padding-left: 60px; padding-right: 60px; }
        .ep-card-wrapper { flex: 0 0 220px; }
        .card-wrapper { width: 200px; }
    }

    .details-page { animation: fadeIn 0.4s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endsection

@section('content')

    @php
        $backdrop = $serie->backdrop_path ? 'https://image.tmdb.org/t/p/original' . $serie->backdrop_path : asset('img/no-backdrop.jpg');
        $ano = $serie->first_air_year ?? ($serie->first_air_date ? date('Y', strtotime($serie->first_air_date)) : 'N/A');
        $generos = $serie->genres->pluck('name')->join(', ') ?: 'Série';
        $classificacao = $serie->age_rating ?: 'Livre';
    @endphp

    <!-- ===== DETAILS PAGE ===== -->
    <div class="details-page">

        <!-- BACKDROP (fixo com efeito de scroll) -->
        <div class="details-backdrop" style="background-image: url('{{ $backdrop }}')"></div>

        <!-- CONTEÚDO SOBREPOSTO -->
        <div class="details-content-wrapper">
            <div class="details-content">
                <span class="badge">📺 Série</span>
                <h1>{{ $serie->name }}</h1>
                <div class="meta">
                    <span class="avaliacao"><i class="fas fa-star"></i> {{ number_format($serie->rating ?? 0, 1) }}</span>
                    <span>{{ $ano }}</span>
                    <span>{{ $serie->number_of_seasons }} Temporadas</span>
                    <span>{{ $generos }}</span>
                </div>
                <div class="sinopse">{{ $serie->overview ?: 'Nenhuma sinopse disponível.' }}</div>
                <div class="classificacao"><span>Classificação:</span> {{ $classificacao }}</div>

                <!-- Botão Assistir (abre o modal de servidores) -->
                @if($serie->seasons->count() > 0 && $serie->seasons->first()->episodes->count() > 0)
                    @php
                        $firstEp = $serie->seasons->first()->episodes->first();
                    @endphp
                    <a href="{{ route('frontend.episode', [$serie->slug, $serie->seasons->first()->season_number, $firstEp->episode_number]) }}" style="text-decoration: none;">
                        <button class="btn-assistir-full"><i class="fas fa-play"></i> Assistir Episódio 1</button>
                    </a>
                @else
                    <button class="btn-assistir-full" onclick="alert('Episódios em breve!')"><i class="fas fa-play"></i> Em Breve</button>
                @endif

                <!-- Ações com ícones -->
                <div class="action-icons">
                    <button onclick="alert('Em desenvolvimento')"><i class="fas fa-comment"></i><span class="label">Comentar</span></button>
                    <button onclick="alert('Em desenvolvimento')"><i class="fas fa-plus"></i><span class="label">Lista</span></button>
                    @if($serie->trailer_key)
                        <button onclick="window.open('https://youtube.com/watch?v={{ $serie->trailer_key }}', '_blank')"><i class="fas fa-film"></i><span class="label">Trailer</span></button>
                    @else
                        <button onclick="alert('Trailer indisponível')"><i class="fas fa-film"></i><span class="label">Trailer</span></button>
                    @endif
                    <button onclick="navigator.share ? navigator.share({title: '{{ $serie->name }}', url: window.location.href}) : alert('Em desenvolvimento')"><i class="fas fa-share-alt"></i><span class="label">Compartilhar</span></button>
                </div>

                <!-- TEMPORADAS -->
                <div class="temporadas-section">
                    <h3><i class="fas fa-list"></i> Temporadas</h3>
                    <div class="temporadas-scroll" id="temporadasContainer">
                        @foreach($serie->seasons as $index => $season)
                            <button class="temp-btn {{ $index === 0 ? 'active' : '' }}" onclick="showSeason({{ $season->id }}, this)">
                                Temporada {{ $season->season_number }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- EPISÓDIOS -->
                <div class="episodios-section" id="episodiosSection">
                    <h3><i class="fas fa-play-circle"></i> Episódios</h3>
                    
                    @foreach($serie->seasons as $index => $season)
                        <div class="episodios-scroll season-episodes" id="season-{{ $season->id }}" style="display: {{ $index === 0 ? 'flex' : 'none' }};">
                            @foreach($season->episodes as $ep)
                                @php
                                    $ep_image = $ep->still_path ? 'https://image.tmdb.org/t/p/w300' . $ep->still_path : ($serie->backdrop_path ? 'https://image.tmdb.org/t/p/w300' . $serie->backdrop_path : asset('img/no-backdrop.jpg'));
                                    $url = route('frontend.episode', [$serie->slug, $season->season_number, $ep->episode_number]);
                                @endphp
                                <div class="ep-card-wrapper" onclick="window.location.href='{{ $url }}'">
                                    <div class="ep-card">
                                        <div class="ep-card-img" style="background-image: url('{{ $ep_image }}')"></div>
                                    </div>
                                    <div class="ep-title">{{ $ep->episode_number }}. {{ $ep->name }}</div>
                                </div>
                            @endforeach
                            @if($season->episodes->count() === 0)
                                <span style="color:#b0b8c4;padding:6px 0;">Nenhum episódio cadastrado nesta temporada.</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- ELENCO -->
                @if($serie->cast && $serie->cast->count() > 0)
                <div class="details-elenco">
                    <h3><i class="fas fa-users"></i> Elenco</h3>
                    <div class="elenco-list" id="elencoList">
                        @foreach($serie->cast->take(10) as $actor)
                            @php
                                $iniciais = substr(strtoupper(preg_replace('/[^A-Za-z]/', '', $actor->name)), 0, 2);
                            @endphp
                            <div class="elenco-item">
                                @if($actor->profile_path)
                                    <div class="foto" style="background-image: url('https://image.tmdb.org/t/p/w185{{ $actor->profile_path }}'); background-color: transparent;"></div>
                                @else
                                    <div class="foto">{{ $iniciais }}</div>
                                @endif
                                <div class="nome">{{ $actor->name }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- RELACIONADOS -->
                <div class="relacionados">
                    <h3><i class="fas fa-play-circle"></i> Relacionados</h3>
                    <div class="scroll-horizontal">
                        @foreach($related as $content)
                            @php
                                $isMovie = isset($content->title);
                                $title = $isMovie ? $content->title : $content->name;
                                $image = $content->poster_path ? 'https://image.tmdb.org/t/p/w500' . $content->poster_path : asset('img/no-poster.jpg');
                                $url = $isMovie ? route('frontend.movie', $content->slug) : route('frontend.serie', $content->slug);
                                $nota = $content->vote_average ?? ($content->rating ?? 0);
                                $anoC = $isMovie ? ($content->release_date ? date('Y', strtotime($content->release_date)) : $content->release_year) : ($content->first_air_date ? date('Y', strtotime($content->first_air_date)) : $content->first_air_year);
                                $duracao = $isMovie ? ($content->runtime ?? 0) : 0;
                                $sinopse = $content->overview ?? 'Sinopse não disponível.';
                            @endphp
                            <div class="card-wrapper catalog-card" 
                                data-titulo="{{ $title }}" 
                                data-ano="{{ $anoC }}" 
                                data-nota="{{ $nota }}" 
                                data-duracao="{{ $duracao }}" 
                                data-img="{{ $image }}" 
                                data-sinopse="{{ $sinopse }}"
                                data-url="{{ $url }}"
                                onclick="window.location.href='{{ $url }}'">
                                <div class="card">
                                    <div class="card-img" style="background-image: url('{{ $image }}')"></div>
                                </div>
                                <div class="card-title">{{ $title }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function showSeason(seasonId, btnElement) {
        // Atualiza botões
        document.querySelectorAll('.temp-btn').forEach(b => b.classList.remove('active'));
        btnElement.classList.add('active');

        // Mostra apenas a lista de episódios da temporada
        document.querySelectorAll('.season-episodes').forEach(c => c.style.display = 'none');
        const activeSeason = document.getElementById('season-' + seasonId);
        if(activeSeason) {
            activeSeason.style.display = 'flex';
        }
    }
</script>
@endsection

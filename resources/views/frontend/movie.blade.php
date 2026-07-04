@extends('layouts.fyne')

@section('title', 'Assistir ' . $movie->title . ' Online')

@section('seo')
    <meta name="description" content="{{ Str::limit($movie->overview, 150) }}">
    <meta property="og:title" content="Assistir {{ $movie->title }} Online - FYNECINE">
    <meta property="og:description" content="{{ Str::limit($movie->overview, 150) }}">
    <meta property="og:image" content="{{ $movie->backdrop_path ? 'https://image.tmdb.org/t/p/w1280' . $movie->backdrop_path : asset('img/no-backdrop.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="video.movie">
    
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Assistir {{ $movie->title }} Online - FYNECINE">
    <meta name="twitter:description" content="{{ Str::limit($movie->overview, 150) }}">
    <meta name="twitter:image" content="{{ $movie->backdrop_path ? 'https://image.tmdb.org/t/p/w1280' . $movie->backdrop_path : asset('img/no-backdrop.jpg') }}">
@endsection
@section('styles')
<style>
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

    /* ----- RELACIONADOS (cards com título fora e alinhado à esquerda) ----- */
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

    /* Wrapper do card + título - tamanho fixo para todos */
    .card-wrapper {
        width: 140px;
        flex-shrink: 0;
        flex-grow: 0;
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

    /* Card (apenas a imagem) */
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

    /* Título fora do card, alinhado à esquerda, com truncamento */
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

    /* ===== MODAL DE SERVIDORES / PLAYER ===== */
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
    .modal-header h2 {
        font-size: 20px;
        font-weight: 700;
        color: #f0f2f5;
    }
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

    .modal-body {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

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
    .server-item .server-name {
        font-weight: 700;
        font-size: 15px;
        color: #f0f2f5;
    }
    .server-item .server-details {
        display: flex;
        gap: 12px;
        font-size: 12px;
        color: #b0b8c4;
        flex-wrap: wrap;
    }
    .server-item .server-details span {
        background: #1a1a1a;
        padding: 2px 10px;
        border-radius: 20px;
        border: 1px solid #2a2a2a;
    }
    .server-item .server-details .quality { color: #fbbf24; border-color: #7c3aed; }
    .server-item .server-details .audio { color: #6ee7b7; border-color: #0d9488; }
    .server-item .server-details .type { color: #93c5fd; border-color: #2563eb; }
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
    .server-item .server-action i { font-size: 16px; }

    .modal-footer {
        margin-top: 16px;
        padding-top: 12px;
        border-top: 1px solid #1a1a1a;
        text-align: center;
        font-size: 12px;
        color: #6b7385;
    }

    /* Responsividade geral */
    @media (max-width: 600px) {
        .details-backdrop {
            height: 50vh;
            min-height: 280px;
            max-height: 400px;
            padding: 16px 16px 0;
        }
        .details-content-wrapper {
            margin-top: -15vh;
            padding: 0 16px 20px;
        }
        .details-content h1 { font-size: 20px; margin-bottom: 2px; }
        .details-content .meta { font-size: 11px; gap: 8px; margin-bottom: 6px; }
        .details-content .sinopse { font-size: 13px; line-height: 1.4; margin-bottom: 8px; }
        .details-content .classificacao { font-size: 12px; margin-bottom: 12px; }
        .details-content .badge { font-size: 10px; padding: 3px 10px; margin-bottom: 4px; }
        .btn-assistir-full {
            width: 100%;
            border-radius: 30px;
            padding: 12px;
            font-size: 14px;
            margin: 0 0 10px 0;
        }
        .action-icons { gap: 10px; justify-content: space-around; }
        .action-icons button { font-size: 10px; }
        .action-icons button i { font-size: 18px; }
        .action-icons button .label { font-size: 8px; }
        .elenco-item { flex: 0 0 56px; }
        .elenco-item .foto { width: 56px; height: 56px; font-size: 14px; }
        .elenco-item .nome { font-size: 9px; }
        .card-wrapper { width: 100px; gap: 4px; }
        .card-title { font-size: 11px; }
        .scroll-horizontal { gap: 8px; padding-bottom: 4px; }
        .relacionados { padding: 0 0 4px 0; }
        .details-elenco { padding: 0 0 12px 0; }
    }

    @media (min-width: 601px) {
        .action-icons { gap: 28px; }
        .btn-assistir-full { width: auto; min-width: 200px; }
        .details-content-wrapper { padding-left: 40px; padding-right: 40px; }
        .details-content h1 { font-size: 38px; }
        .details-content .sinopse { font-size: 16px; max-width: 800px; }
        .card-wrapper { width: 180px; }
    }

    @media (min-width: 1025px) {
        .details-backdrop { max-height: 600px; }
        .details-content h1 { font-size: 46px; }
        .details-content .sinopse { font-size: 17px; max-width: 900px; }
        .details-content-wrapper { padding-left: 60px; padding-right: 60px; }
        .card-wrapper { width: 200px; }
    }

    .details-page { animation: fadeIn 0.4s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    /* Header Override Fix */
    .header {
        position: fixed !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        background: linear-gradient(180deg, rgba(0,0,0,0.85) 0%, transparent 100%) !important;
    }
</style>
@endsection

@section('content')
    @php
        $settings = \App\Models\AppConfig::getSettings();
        $backdrop = $movie->backdrop_path ? 'https://image.tmdb.org/t/p/original' . $movie->backdrop_path : asset('img/no-backdrop.jpg');
        $ano = $movie->release_year ?? 'N/A';
        $generos = $movie->genres->pluck('name')->join(', ') ?: 'Filme';
        $classificacao = $movie->age_rating ?: 'Livre';
    @endphp

    <!-- ===== DETAILS PAGE ===== -->
    <div class="details-page">

        <!-- BACKDROP -->
        <div class="details-backdrop" style="background-image: url('{{ $backdrop }}')"></div>

        <!-- CONTEÚDO SOBREPOSTO -->
        <div class="details-content-wrapper">
            <div class="details-content">
                <span class="badge">Filme</span>
                <h1>{{ $movie->title }}</h1>
                <div class="meta">
                    <span class="avaliacao"><i class="fas fa-star"></i> {{ number_format($movie->rating ?? 0, 1) }}</span>
                    <span>{{ $ano }}</span>
                    <span>{{ $movie->runtime }} min</span>
                    <span>{{ $generos }}</span>
                </div>
                <div class="sinopse">{{ $movie->overview ?: 'Nenhuma sinopse disponível.' }}</div>
                <div class="classificacao"><span>Classificação:</span> {{ $classificacao }}</div>

                <!-- Botão Assistir -->
                <button class="btn-assistir-full" id="btnOpenModal"><i class="fas fa-play"></i> Assistir agora</button>

                <!-- Ações com ícones -->
                <div class="action-icons">
                    {{-- Comentar - em desenvolvimento --}}
                    {{-- <button><i class="fas fa-comment"></i><span class="label">Comentar</span></button> --}}
                    {{-- Minha Lista - em desenvolvimento --}}
                    {{-- <button><i class="fas fa-plus"></i><span class="label">Lista</span></button> --}}
                    @if($movie->trailer_url)
                        <button onclick="window.open('{{ $movie->trailer_url }}', '_blank')"><i class="fas fa-film"></i><span class="label">Trailer</span></button>
                    @else
                        <button><i class="fas fa-film"></i><span class="label">Trailer</span></button>
                    @endif
                    <button><i class="fas fa-share-alt"></i><span class="label">Compartilhar</span></button>
                </div>

                <!-- ELENCO -->
                @if($movie->cast && $movie->cast->count() > 0)
                <div class="details-elenco">
                    <h3><i class="fas fa-users"></i> Elenco</h3>
                    <div class="elenco-list">
                        @foreach($movie->cast as $actor)
                        <div class="elenco-item">
                            @if($actor->profile_path)
                                <div class="foto" style="background-image: url('{{ $actor->profile_path }}')"></div>
                            @else
                                <div class="foto">{{ strtoupper(substr($actor->name, 0, 2)) }}</div>
                            @endif
                            <div class="nome">{{ $actor->name }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- RELACIONADOS -->
                @if($related && $related->count() > 0)
                <div class="relacionados">
                    <h3><i class="fas fa-play-circle"></i> Relacionados</h3>
                    <div class="scroll-horizontal">
                        @foreach($related as $rel)
                            @php
                                $relImage = $rel->poster_path ? 'https://image.tmdb.org/t/p/w500' . $rel->poster_path : asset('img/no-poster.jpg');
                                $relBackdrop = $rel->backdrop_path ? 'https://image.tmdb.org/t/p/w780' . $rel->backdrop_path : '';
                                $relUrl = route('frontend.movie', $rel->slug);
                                $relAno = $rel->release_year ?? ($rel->release_date ? date('Y', strtotime($rel->release_date)) : '');
                                $relNota = $rel->rating ?? $rel->vote_average ?? 0;
                                $relDuracao = $rel->runtime ?? 0;
                                $relSinopse = $rel->overview ?? 'Sinopse não disponível.';
                            @endphp
                            <div class="card-wrapper">
                                <div class="card"
                                     data-titulo="{{ $rel->title }}"
                                     data-ano="{{ $relAno }}"
                                     data-nota="{{ $relNota }}"
                                     data-duracao="{{ $relDuracao }}"
                                     data-img="{{ $relImage }}"
                                     data-backdrop="{{ $relBackdrop }}"
                                     data-sinopse="{{ $relSinopse }}"
                                     data-url="{{ $relUrl }}"
                                     onclick="window.location.href='{{ $relUrl }}'">
                                    <div class="card-img" style="background-image: url('{{ $relImage }}')"></div>
                                </div>
                                <div class="card-title">{{ $rel->title }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ===== MODAL DE SERVIDORES ===== -->
    <div class="modal-overlay" id="serversModal">
        <div class="modal-container" id="modalContainer">
            <div class="modal-header">
                <h2><i class="fas fa-server"></i> Assistir: {{ $movie->title }}</h2>
                <button class="modal-close" id="modalClose"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="modal-body" id="serverList">
                @if($settings->autoembed_movies && $movie->tmdb_id && $movie->use_autoembed)
                    @php
                        $sources = is_string($settings->autoembed_movie_sources) ? json_decode($settings->autoembed_movie_sources, true) : $settings->autoembed_movie_sources;
                        $sources = $sources ?: [];
                    @endphp
                    @foreach($sources as $source)
                        @php
                            if(($source['player_sub'] ?? '') === 'premium' || ($source['type'] ?? '') === 'premium') continue;
                            $autoEmbedUrl = str_replace('{tmdb_id}', $movie->tmdb_id, $source['url']);
                        @endphp
                        <div class="server-item" onclick="playVideo('{{ $autoEmbedUrl }}')">
                            <div class="server-info">
                                <div class="server-name">{{ $source['name'] ?? 'Servidor Automático' }}</div>
                                <div class="server-details">
                                    <span class="quality">{{ $source['quality'] ?? 'Auto' }}</span>
                                    <span class="audio">Dublado</span>
                                    <span class="type">EMBED</span>
                                </div>
                            </div>
                            <div class="server-action"><i class="fas fa-external-link-alt"></i></div>
                        </div>
                    @endforeach
                @endif

                @foreach($movie->playLinks->whereNotIn('type', ['premium', 'private']) as $link)
                    @php
                        $linkUrl = $link->url;
                        if($link->type === 'private' || $link->type === 'mp4') {
                            $linkUrl = \App\Services\BunnyLinkService::generateSignedUrl($linkUrl, $link->link_path, $link->expiration_hours);
                        }
                    @endphp
                    <div class="server-item" onclick="playVideo('{{ $linkUrl }}')">
                        <div class="server-info">
                            <div class="server-name">{{ $link->name ?? 'Servidor ' . ($loop->index + 1) }}</div>
                            <div class="server-details">
                                <span class="quality">{{ $link->quality ?? 'HD' }}</span>
                                <span class="audio">{{ $link->audio ?? 'Dublado' }}</span>
                                <span class="type">{{ strtoupper($link->type) }}</span>
                            </div>
                        </div>
                        <div class="server-action"><i class="fas fa-external-link-alt"></i></div>
                    </div>
                @endforeach

                @if(!$autoEmbedUrl && $movie->playLinks->isEmpty())
                    <div style="text-align:center; padding: 20px; color:#b0b8c4;">Nenhum servidor disponível no momento.</div>
                @endif
            </div>
            <div class="modal-footer">Selecione um servidor, o player abrirá abaixo.</div>
        </div>
    </div>

    <!-- ===== MODAL DO PLAYER ===== -->
    <div class="modal-overlay" id="playerModal">
        <div class="modal-container" style="width: 95%; height: 95%; max-width: 1200px; max-height: 800px; padding: 0; display: flex; flex-direction: column;">
            <div class="modal-header" style="padding: 15px 20px; flex-shrink: 0;">
                <h2><i class="fas fa-play"></i> Assistindo: {{ $movie->title }}</h2>
                <button class="modal-close" id="playerModalClose"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="flex: 1; padding: 0; background: #000;">
                <iframe id="playerIframe" src="" style="width: 100%; height: 100%; border: none;" allowfullscreen></iframe>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    const modal = document.getElementById('serversModal');
    const btnOpen = document.getElementById('btnOpenModal');
    const btnClose = document.getElementById('modalClose');

    // Abre Modal
    btnOpen.addEventListener('click', () => {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    });

    // Fecha Modal
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    btnClose.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    // Função para tocar o vídeo no novo Modal
    function playVideo(url) {
        if(!url) {
            alert('URL do vídeo não encontrada.');
            return;
        }
        // Fecha modal de servidores
        closeModal();
        // Abre modal do player
        document.getElementById('playerIframe').src = url;
        document.getElementById('playerModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    // Fechar Modal do Player
    document.getElementById('playerModalClose').addEventListener('click', () => {
        document.getElementById('playerModal').classList.remove('active');
        document.body.style.overflow = '';
        // Limpa o src para parar de tocar o vídeo/áudio
        document.getElementById('playerIframe').src = '';
    });
</script>
@endsection

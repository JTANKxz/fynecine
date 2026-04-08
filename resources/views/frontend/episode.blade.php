@extends('layouts.app', [
    'title' => 'Assistir ' . $serie->name . ' - Temporada ' . $season_number . ' Episódio ' . $episode_number . ' Online - Fynecine',
    'description' => 'Assistir ' . $serie->name . ' S' . $season_number . 'E' . $episode_number . ' online grátis. ' . Str::limit($episode->overview, 150),
    'image' => $episode->still_path ? 'https://image.tmdb.org/t/p/w780' . $episode->still_path : ($serie->backdrop_path ? 'https://image.tmdb.org/t/p/w780' . $serie->backdrop_path : null)
])

@section('content')
    @php
        $settings = \App\Models\AppConfig::getSettings();
        $autoEmbedUrl = null;
        if($settings->is_autoembed_active && $serie->tmdb_id && $serie->use_autoembed) {
            $autoEmbedUrl = str_replace(
                ['{id}', '{s}', '{e}'], 
                [$serie->tmdb_id, $season_number, $episode_number], 
                $settings->autoembed_serie_url
            );
        }
    @endphp

    <div class="content-hero" style="height: 50vh;">
        <img src="{{ $episode->still_path ? 'https://image.tmdb.org/t/p/original' . $episode->still_path : ($serie->backdrop_path ? 'https://image.tmdb.org/t/p/original' . $serie->backdrop_path : asset('img/no-backdrop.jpg')) }}" alt="{{ $episode->name }}" class="hero-backdrop">
        <div class="hero-overlay"></div>
        
        <!-- Center Play Button -->
        @php
            $firstLink = $episode->links->first();
            $firstUrl = $firstLink ? $firstLink->url : '';
            if($firstLink && ($firstLink->type === 'private' || $firstLink->type === 'mp4')) {
                $firstUrl = \App\Services\BunnyLinkService::generateSignedUrl($firstUrl, $firstLink->link_path, $firstLink->expiration_hours);
            }
            $finalTriggerUrl = $autoEmbedUrl ?? $firstUrl;
        @endphp
        <div class="hero-play-trigger" onclick="openPlayer('{{ $finalTriggerUrl }}')">
            <div class="play-icon-circle">
                <i class="fas fa-play"></i>
            </div>
        </div>

        <div class="container content-details" style="bottom: 50px;">
            <div class="details-info">
                <h2 style="color: var(--primary-purple); margin-bottom: 0.5rem;">{{ $serie->name }}</h2>
                <h1>T{{ $season_number }} E{{ $episode_number }} - {{ $episode->name }}</h1>
                <p class="overview">{{ $episode->overview }}</p>
            </div>
        </div>
    </div>

    <div class="container content-body">
        <div class="navigation-buttons" style="display: flex; justify-content: space-between; margin-bottom: 2rem;">
            @php
                $prevEp = $serie->episodes()
                    ->where('season_number', $season_number)
                    ->where('episode_number', '<', $episode_number)
                    ->orderBy('episode_number', 'desc')
                    ->first();
                $nextEp = $serie->episodes()
                    ->where('season_number', $season_number)
                    ->where('episode_number', '>', $episode_number)
                    ->orderBy('episode_number', 'asc')
                    ->first();
            @endphp

            @if($prevEp)
                <a href="{{ route('frontend.episode', [$serie->slug, $season_number, $prevEp->episode_number]) }}" class="server-btn">
                    <i class="fas fa-arrow-left"></i> Anterior
                </a>
            @else
                <div></div>
            @endif

            @if($nextEp)
                <a href="{{ route('frontend.episode', [$serie->slug, $season_number, $nextEp->episode_number]) }}" class="server-btn">
                    Próximo <i class="fas fa-arrow-right"></i>
                </a>
            @endif
        </div>

        <section class="section">
            <h2 class="section-title">Outros Episódios</h2>
            <div class="horizontal-scroll">
                @foreach($serie->episodes()->where('season_number', $season_number)->get() as $ep)
                    <a href="{{ route('frontend.episode', [$serie->slug, $season_number, $ep->episode_number]) }}" class="movie-card {{ $ep->id == $episode->id ? 'active' : '' }}">
                        <img src="{{ $ep->still_path ? 'https://image.tmdb.org/t/p/w300' . $ep->still_path : ($serie->backdrop_path ? 'https://image.tmdb.org/t/p/w300' . $serie->backdrop_path : asset('img/no-backdrop.jpg')) }}" alt="{{ $ep->name }}" class="card-image">
                        <div class="card-info">
                            <div class="card-title">Ep. {{ $ep->episode_number }} - {{ $ep->name }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <!-- Player Modal -->
    <div id="player-modal" class="player-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="modal-title">{{ $serie->name }} - {{ $episode->name }}</h3>
                <div class="btn-close-modal" onclick="closePlayer()">
                    <i class="fas fa-times"></i>
                </div>
            </div>
            
            <div class="modal-iframe-wrapper">
                <iframe id="player-iframe" src="" allowfullscreen scrolling="no"></iframe>
            </div>

            <div class="server-selector">
                @if($autoEmbedUrl)
                    <button class="server-btn active" onclick="changeModalServer('{{ $autoEmbedUrl }}', this)">
                        AutoEmbed
                    </button>
                @endif

                @foreach($episode->links as $link)
                    @php
                        $linkUrl = $link->url;
                        if($link->type === 'private' || $link->type === 'mp4') {
                            $linkUrl = \App\Services\BunnyLinkService::generateSignedUrl($linkUrl, $link->link_path, $link->expiration_hours);
                        }
                    @endphp
                    <button class="server-btn {{ !$autoEmbedUrl && $loop->first ? 'active' : '' }}" onclick="changeModalServer('{{ $linkUrl }}', this)">
                        {{ $link->name ?? 'Servidor ' . ($loop->index + 1) }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function openPlayer(url) {
            if(!url) {
                alert('Nenhum link de reprodução disponível.');
                return;
            }
            const modal = document.getElementById('player-modal');
            const iframe = document.getElementById('player-iframe');
            
            iframe.src = url;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePlayer() {
            const modal = document.getElementById('player-modal');
            const iframe = document.getElementById('player-iframe');
            
            iframe.src = '';
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function changeModalServer(url, btn) {
            document.getElementById('player-iframe').src = url;
            document.querySelectorAll('.server-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
    </script>
@endsection

@extends('layouts.app', [
    'title' => 'Assistir ' . $movie->title . ' Online Grátis Dublado - Fynecine',
    'description' => 'Assistir ' . $movie->title . ' online grátis. ' . Str::limit($movie->overview, 150),
    'image' => $movie->backdrop_path ? 'https://image.tmdb.org/t/p/w780' . $movie->backdrop_path : null
])

@section('content')
    @php
        $settings = \App\Models\AppConfig::getSettings();
        $autoEmbedUrl = null;
        if($settings->autoembed_movies && $movie->tmdb_id && $movie->use_autoembed) {
            $autoEmbedUrl = str_replace('{id}', $movie->tmdb_id, $settings->autoembed_movie_url);
        }
    @endphp

    <div class="content-hero">
        <img src="{{ $movie->backdrop_path ? 'https://image.tmdb.org/t/p/original' . $movie->backdrop_path : asset('img/no-backdrop.jpg') }}" alt="{{ $movie->title }}" class="hero-backdrop">
        <div class="hero-overlay"></div>
        
        <!-- Center Play Button -->
        @php
            $firstLink = $movie->playLinks->first();
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

        <div class="container content-details">
            <div class="details-flex">
                <img src="{{ $movie->poster_path ? 'https://image.tmdb.org/t/p/w500' . $movie->poster_path : asset('img/no-poster.jpg') }}" alt="{{ $movie->title }}" class="details-poster">
                <div class="details-info">
                    <h1>{{ $movie->title }}</h1>
                    <div class="details-meta">
                        <span><i class="fas fa-calendar"></i> {{ $movie->release_year }}</span>
                        <span><i class="fas fa-star" style="color: gold;"></i> {{ number_format($movie->rating, 1) }}</span>
                        <span><i class="fas fa-clock"></i> {{ $movie->runtime }} min</span>
                        <span><i class="fas fa-eye"></i> {{ $movie->views_count ?? 0 }} visualizações</span>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        @foreach($movie->genres as $genre)
                            <a href="{{ route('frontend.genre', $genre->slug) }}" style="background: var(--primary-purple); padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; margin-right: 5px;">{{ $genre->name }}</a>
                        @endforeach
                    </div>
                    <p class="overview">{{ $movie->overview }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container content-body">
        <section class="section">
            <h2 class="section-title">Conteúdos Relacionados</h2>
            <div class="horizontal-scroll">
                @foreach($related as $item)
                    @include('components.movie-card', ['content' => $item])
                @endforeach
            </div>
        </section>
    </div>

    <!-- Player Modal -->
    <div id="player-modal" class="player-modal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="modal-title">{{ $movie->title }}</h3>
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

                @foreach($movie->playLinks as $link)
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

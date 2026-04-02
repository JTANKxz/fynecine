@extends('layouts.app', [
    'title' => 'Assistir Série ' . $serie->name . ' Grátis Online - Fynecine',
    'description' => 'Assistir ' . $serie->name . ' online grátis. ' . Str::limit($serie->overview, 150),
    'image' => $serie->backdrop_path ? 'https://image.tmdb.org/t/p/w780' . $serie->backdrop_path : null
])

@section('content')
    <div class="content-hero">
        <img src="{{ $serie->backdrop_path ? 'https://image.tmdb.org/t/p/original' . $serie->backdrop_path : asset('img/no-backdrop.jpg') }}" alt="{{ $serie->name }}" class="hero-backdrop">
        <div class="hero-overlay"></div>
        
        <!-- Center Play Button (scrolls to seasons) -->
        <div class="hero-play-trigger" onclick="document.getElementById('seasons').scrollIntoView({behavior: 'smooth'})">
            <div class="play-icon-circle">
                <i class="fas fa-list"></i>
            </div>
        </div>

        <div class="container content-details">
            <div class="details-flex">
                <img src="{{ $serie->poster_path ? 'https://image.tmdb.org/t/p/w500' . $serie->poster_path : asset('img/no-poster.jpg') }}" alt="{{ $serie->name }}" class="details-poster">
                <div class="details-info">
                    <h1>{{ $serie->name }}</h1>
                    <div class="details-meta">
                        <span><i class="fas fa-calendar"></i> {{ $serie->first_air_year }}</span>
                        <span><i class="fas fa-star" style="color: gold;"></i> {{ number_format($serie->rating, 1) }}</span>
                        <span><i class="fas fa-tv"></i> {{ $serie->number_of_seasons }} Temporadas</span>
                        <span><i class="fas fa-eye"></i> {{ $serie->views_count ?? 0 }} visualizações</span>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        @foreach($serie->genres as $genre)
                            <a href="{{ route('frontend.genre', $genre->slug) }}" style="background: var(--primary-purple); padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; margin-right: 5px;">{{ $genre->name }}</a>
                        @endforeach
                    </div>
                    <p class="overview">{{ $serie->overview }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container content-body">
        <div id="seasons" class="seasons-container">
            <div class="tabs">
                @foreach($serie->seasons as $index => $season)
                    <div class="tab-item {{ $index === 0 ? 'active' : '' }}" onclick="showSeason({{ $season->season_number }})">
                        Temporada {{ $season->season_number }}
                    </div>
                @endforeach
            </div>

            @foreach($serie->seasons as $index => $season)
                <div id="season-{{ $season->season_number }}" class="season-content" style="display: {{ $index === 0 ? 'grid' : 'none' }}; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                    @foreach($season->episodes as $episode)
                        @php
                            $ep_image = $episode->still_path ? 'https://image.tmdb.org/t/p/w300' . $episode->still_path : ($serie->backdrop_path ? 'https://image.tmdb.org/t/p/w300' . $serie->backdrop_path : asset('img/no-backdrop.jpg'));
                        @endphp
                        <a href="{{ route('frontend.episode', [$serie->slug, $season->season_number, $episode->episode_number]) }}" class="episode-card">
                            <div style="position: relative;">
                                <img src="{{ $ep_image }}" alt="{{ $episode->name }}" class="episode-image" loading="lazy">
                                <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.8); padding: 2px 8px; border-radius: 4px; font-size: 0.7rem;">
                                    Ep. {{ $episode->episode_number }}
                                </div>
                            </div>
                            <div class="episode-info">
                                <h4 style="margin-bottom: 5px;">{{ $episode->name }}</h4>
                                <p style="font-size: 0.8rem; color: var(--text-muted);">{{ Str::limit($episode->overview, 80) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </div>

        <section class="section">
            <h2 class="section-title">Séries Relacionadas</h2>
            <div class="horizontal-scroll">
                @foreach($related as $item)
                    @include('components.movie-card', ['content' => $item])
                @endforeach
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        function showSeason(number) {
            document.querySelectorAll('.tab-item').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');

            document.querySelectorAll('.season-content').forEach(c => c.style.display = 'none');
            document.getElementById('season-' + number).style.display = 'grid';
        }
    </script>
@endsection

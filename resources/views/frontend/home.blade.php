@extends('layouts.frontend')

@php
    $settings = \App\Models\AppConfig::getSettings();
@endphp

@section('content')
    <!-- Hero Slider -->
    @if($sliders->isNotEmpty())
        <section class="hero">
            @foreach($sliders as $index => $slider)
                @php
                    $content = $slider->movie ?? $slider->serie;
                    if (!$content) continue;
                    $title = $content->title ?? $content->name;
                    $image = $content->backdrop_path ? 'https://image.tmdb.org/t/p/original' . $content->backdrop_path : asset('img/no-backdrop.jpg');
                    $url = $slider->content_type === 'movie' ? route('frontend.movie', $content->slug) : route('frontend.serie', $content->slug);
                @endphp
                <div class="hero-slide {{ $index === 0 ? 'active' : '' }}">
                    <div class="hero-bg">
                        <img src="{{ $image }}" alt="Assistir {{ $title }} Online">
                        <div class="hero-overlay"></div>
                    </div>
                    <div class="container">
                        <div class="hero-content">
                            <h2 class="hero-title">{{ $title }}</h2>
                            <p style="color: var(--text-secondary); margin-bottom: 2rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $content->overview }}
                            </p>
                            <a href="{{ $url }}" class="btn-play">
                                <i class="fas fa-play"></i> Assistir Agora
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </section>
    @endif

    <div class="container" style="margin-top: 2rem;">
        
        <!-- Keywords SEO (Subtle but presence for indices) -->
        <h1 style="font-size: 1rem; color: #111; height: 1px; overflow: hidden;">
            Assistir filmes online, assistir filmes online egrátis, assistir series online gratis, assistir filmes online dublado
        </h1>

        <!-- Gêneros Scroll -->
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">Gêneros Populares</h2>
            </div>
            <div class="genre-chips">
                @foreach($genres as $genre)
                    <a href="{{ route('frontend.genre', $genre->slug) }}" class="genre-chip" title="Filmes de {{ $genre->name }}">
                        {{ $genre->name }}
                    </a>
                @endforeach
            </div>
        </section>

        <!-- Filmes Section -->
        <section class="section" style="margin-top: 2rem;">
            <div class="section-header">
                <h2 class="section-title">Últimos Filmes Adicionados</h2>
                <a href="{{ route('frontend.search') }}" style="font-size: 0.9rem; color: var(--text-secondary);">Ver todos</a>
            </div>
            <div class="scroll-container">
                @foreach($latestMovies as $movie)
                    @include('components.movie-card-seo', ['content' => $movie])
                @endforeach
            </div>
        </section>

        <!-- Séries Section -->
        <section class="section" style="margin-top: 3rem;">
            <div class="section-header">
                <h2 class="section-title">Últimas Séries Adicionadas</h2>
                <a href="{{ route('frontend.search') }}" style="font-size: 0.9rem; color: var(--text-secondary);">Ver todas</a>
            </div>
            <div class="scroll-container">
                @foreach($latestSeries as $serie)
                    @include('components.movie-card-seo', ['content' => $serie])
                @endforeach
            </div>
        </section>

        <!-- SEO Text Block -->
        <section class="section" style="margin-top: 5rem; padding: 3rem; background: #0a0a0a; border-radius: 20px; text-align: center;">
            <h2 style="font-size: 1.8rem; margin-bottom: 1.5rem;">Sua melhor opção para assistir filmes e séries online</h2>
            <p style="color: var(--text-secondary); max-width: 800px; margin: 0 auto; font-size: 1rem; line-height: 1.8;">
                Bem-vindo ao <strong>{{ $settings->app_name }}</strong>, o lugar definitivo para quem busca <strong>assistir filmes online egrátis</strong> e curtir as melhores <strong>séries online gratis</strong>. Nossa plataforma oferece um catálogo vasto, sempre atualizado com os últimos lançamentos de Hollywood e do cinema mundial, tudo em <strong>HD</strong> e com opções de áudio <strong>dublado</strong> e legendado. Se você quer <strong>assistir filmes online dublado</strong> com rapidez e sem travamentos, está no lugar certo!
            </p>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        // Custom horizontal scroll logic with mouse wheel support if desired
        const scrolls = document.querySelectorAll('.scroll-container, .genre-chips');
        scrolls.forEach(scroll => {
            scroll.addEventListener('wheel', (evt) => {
                evt.preventDefault();
                scroll.scrollLeft += evt.deltaY;
            });
        });
    </script>
@endsection

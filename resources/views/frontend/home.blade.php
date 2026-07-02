@extends('layouts.fyne')

@section('nav_home_active', 'active')

@section('styles')
<style>
    /* ----- CARROSSEL (SLIDER) ----- */
    .hero-slider {
        position: relative;
        width: 100%;
        height: 60vh;
        max-height: 500px;
        min-height: 350px;
        overflow: hidden;
    }
    .slides-wrapper {
        display: flex;
        height: 100%;
        transition: transform 0.5s ease;
    }
    .slide {
        min-width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        position: relative;
    }
    .slide::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(0deg, #000000 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.1) 100%);
    }
    .slide-content {
        position: absolute;
        bottom: 40px;
        left: 20px;
        right: 20px;
        z-index: 2;
    }
    .badge {
        background: #7c3aed;
        color: #fff;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-block;
        margin-bottom: 8px;
    }
    .slide h2 {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 6px;
        line-height: 1.1;
    }
    .slide p {
        font-size: 13px;
        color: #b0b8c4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 12px;
        max-width: 90%;
    }
    .meta-mini {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 12px;
        color: #fff;
        margin-bottom: 16px;
    }
    .meta-mini .avaliacao {
        color: #fbbf24;
        font-weight: 600;
    }
    .btn-play {
        background: #f0f2f5;
        color: #000;
        border: none;
        padding: 10px 24px;
        border-radius: 30px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: 0.2s;
    }
    .btn-play:hover {
        background: #fff;
        transform: scale(1.05);
    }
    .slider-dots {
        position: absolute;
        bottom: 15px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 6px;
        z-index: 10;
    }
    .slider-dots span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(255,255,255,0.4);
        cursor: pointer;
        transition: 0.3s;
    }
    .slider-dots span.active {
        width: 20px;
        background: #7c3aed;
        border-radius: 4px;
    }

    /* ----- SEÇÕES (EM ALTA, ORIGINAIS, ETC) ----- */
    .section {
        padding: 20px 0 10px;
    }
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
        margin-bottom: 12px;
    }
    .section-header h3 {
        font-size: 18px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-header h3 i {
        color: #7c3aed;
    }
    .ver-todos {
        font-size: 13px;
        font-weight: 600;
        color: #a855f7;
        text-decoration: none;
    }

    /* ----- SCROLL HORIZONTAL (CARDS) ----- */
    .scroll-horizontal {
        display: flex;
        overflow-x: auto;
        gap: 14px;
        padding: 0 20px 20px;
        scrollbar-width: none; /* Firefox */
    }
    .scroll-horizontal::-webkit-scrollbar {
        display: none; /* Chrome */
    }
    .card {
        min-width: 130px;
        width: 130px;
        flex-shrink: 0;
        aspect-ratio: 2/3;
        border-radius: 12px;
        background: #1a1a1a;
        position: relative;
        cursor: pointer;
        overflow: hidden;
        transition: transform 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .card:hover {
        transform: scale(1.05);
        z-index: 10;
    }
    .card-img {
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        transition: 0.3s;
    }
</style>
@endsection

@section('content')
    <!-- CARROSSEL HERO -->
    @if($sliders->isNotEmpty())
    <section class="hero-slider" id="heroSlider">
        <div class="slides-wrapper" id="slidesWrapper">
            @foreach($sliders as $index => $slider)
                @php
                    $content = $slider->movie ?? $slider->serie;
                    if (!$content) continue;
                    $title = $content->title ?? $content->name;
                    $image = $content->backdrop_path ? 'https://image.tmdb.org/t/p/w1280' . $content->backdrop_path : asset('img/no-backdrop.jpg');
                    $url = $slider->content_type === 'movie' ? route('frontend.movie', $content->slug) : route('frontend.serie', $content->slug);
                    $nota = $content->vote_average ?? 0;
                    $ano = $content->release_date ? date('Y', strtotime($content->release_date)) : ($content->first_air_date ? date('Y', strtotime($content->first_air_date)) : '');
                    $duracao = $content->runtime ? $content->runtime . ' min' : '';
                @endphp
                <div class="slide" style="background-image: url('{{ $image }}');">
                    <div class="slide-content">
                        <span class="badge">✨ Destaque</span>
                        <h2>{{ $title }}</h2>
                        <p>{{ $content->overview }}</p>
                        <div class="meta-mini">
                            <span class="avaliacao"><i class="fas fa-star"></i> {{ number_format($nota, 1) }}</span>
                            <span>{{ $ano }}</span>
                            @if($duracao)
                                <span>{{ $duracao }}</span>
                            @endif
                        </div>
                        <a href="{{ $url }}" style="text-decoration: none;">
                            <button class="btn-play"><i class="fas fa-play"></i> Assistir</button>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="slider-dots" id="sliderDots">
            @foreach($sliders as $index => $slider)
                @if($slider->movie || $slider->serie)
                    <span class="{{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}"></span>
                @endif
            @endforeach
        </div>
    </section>
    @endif

    <!-- SEÇÕES -->
    <section class="section">
        <div class="section-header">
            <h3><i class="fas fa-fire"></i> Em Alta</h3>
            <a href="{{ route('frontend.search') }}" class="ver-todos">Ver todos →</a>
        </div>
        <div class="scroll-horizontal" id="scrollEmAlta">
            @foreach($latestMovies as $movie)
                @php
                    $image = $movie->poster_path ? 'https://image.tmdb.org/t/p/w500' . $movie->poster_path : asset('img/no-poster.jpg');
                    $url = route('frontend.movie', $movie->slug);
                    $nota = $movie->vote_average ?? 0;
                    $ano = $movie->release_date ? date('Y', strtotime($movie->release_date)) : '';
                    $duracao = $movie->runtime ?? 0;
                    $sinopse = $movie->overview ?? 'Sinopse não disponível.';
                @endphp
                <div class="card" 
                     data-titulo="{{ $movie->title }}" 
                     data-ano="{{ $ano }}" 
                     data-nota="{{ $nota }}" 
                     data-duracao="{{ $duracao }}" 
                     data-img="{{ $image }}" 
                     data-sinopse="{{ $sinopse }}"
                     data-url="{{ $url }}"
                     onclick="window.location.href='{{ $url }}'">
                    <div class="card-img" style="background-image: url('{{ $image }}')"></div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <h3><i class="fas fa-star"></i> Originais</h3>
            <a href="{{ route('frontend.search') }}" class="ver-todos">Ver todos →</a>
        </div>
        <div class="scroll-horizontal" id="scrollOriginais">
            @foreach($latestSeries as $serie)
                @php
                    $image = $serie->poster_path ? 'https://image.tmdb.org/t/p/w500' . $serie->poster_path : asset('img/no-poster.jpg');
                    $url = route('frontend.serie', $serie->slug);
                    $nota = $serie->vote_average ?? 0;
                    $ano = $serie->first_air_date ? date('Y', strtotime($serie->first_air_date)) : '';
                    $duracao = 0;
                    $sinopse = $serie->overview ?? 'Sinopse não disponível.';
                @endphp
                <div class="card" 
                     data-titulo="{{ $serie->name }}" 
                     data-ano="{{ $ano }}" 
                     data-nota="{{ $nota }}" 
                     data-duracao="{{ $duracao }}" 
                     data-img="{{ $image }}" 
                     data-sinopse="{{ $sinopse }}"
                     data-url="{{ $url }}"
                     onclick="window.location.href='{{ $url }}'">
                    <div class="card-img" style="background-image: url('{{ $image }}')"></div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <h3><i class="fas fa-play-circle"></i> Continue Assistindo</h3>
            <a href="{{ route('frontend.search') }}" class="ver-todos">Ver todos →</a>
        </div>
        <div class="scroll-horizontal" id="scrollContinue">
            @foreach($latestMovies->merge($latestSeries)->shuffle()->take(10) as $content)
                @php
                    $isMovie = isset($content->title);
                    $title = $isMovie ? $content->title : $content->name;
                    $image = $content->poster_path ? 'https://image.tmdb.org/t/p/w500' . $content->poster_path : asset('img/no-poster.jpg');
                    $url = $isMovie ? route('frontend.movie', $content->slug) : route('frontend.serie', $content->slug);
                    $nota = $content->vote_average ?? 0;
                    $ano = $isMovie ? ($content->release_date ? date('Y', strtotime($content->release_date)) : '') : ($content->first_air_date ? date('Y', strtotime($content->first_air_date)) : '');
                    $duracao = $isMovie ? ($content->runtime ?? 0) : 0;
                    $sinopse = $content->overview ?? 'Sinopse não disponível.';
                @endphp
                <div class="card" 
                     data-titulo="{{ $title }}" 
                     data-ano="{{ $ano }}" 
                     data-nota="{{ $nota }}" 
                     data-duracao="{{ $duracao }}" 
                     data-img="{{ $image }}" 
                     data-sinopse="{{ $sinopse }}"
                     data-url="{{ $url }}"
                     onclick="window.location.href='{{ $url }}'">
                    <div class="card-img" style="background-image: url('{{ $image }}')"></div>
                </div>
            @endforeach
        </div>
    </section>
@endsection

@section('scripts')
<script>
    // ----- CARROSSEL (SLIDER) -----
    const slidesWrapper = document.getElementById('slidesWrapper');
    const dots = document.querySelectorAll('#sliderDots span');
    let currentSlide = 0;
    const totalSlides = dots.length;
    let autoSlideInterval;

    function goToSlide(index) {
        if (totalSlides <= 1) return;
        if (index < 0) index = totalSlides - 1;
        if (index >= totalSlides) index = 0;
        currentSlide = index;
        slidesWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentSlide);
        });
    }
    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            goToSlide(parseInt(dot.dataset.index));
            resetAutoSlide();
        });
    });

    function nextSlide() { goToSlide(currentSlide + 1); }

    function resetAutoSlide() {
        clearInterval(autoSlideInterval);
        if (totalSlides > 1) {
            autoSlideInterval = setInterval(nextSlide, 6000);
        }
    }
    
    if (totalSlides > 1) {
        autoSlideInterval = setInterval(nextSlide, 6000);
        const hero = document.getElementById('heroSlider');
        if (hero) {
            hero.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
            hero.addEventListener('mouseleave', () => { autoSlideInterval = setInterval(nextSlide, 6000); });
        }
    }
</script>
@endsection

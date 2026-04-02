@extends('layouts.app')

@section('content')
    <!-- Hero Slider - 16:9 Perfect Fixed -->
    <div class="hero-slider">
        @foreach($sliders as $index => $slider)
            @php
                $content = $slider->movie ?? $slider->serie;
                if (!$content) continue;
                $title = $content->title ?? $content->name;
                $image = $content->backdrop_path ? 'https://image.tmdb.org/t/p/original' . $content->backdrop_path : asset('img/no-backdrop.jpg');
                $route = $slider->content_type === 'movie' ? route('frontend.movie', $content->slug) : route('frontend.serie', $content->slug);
            @endphp
            <div class="slider-item {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
                <div class="slider-bg-blur" style="background-image: url('{{ $image }}')"></div>
                <img src="{{ $image }}" alt="{{ $title }}" class="slider-image-main">
                <div class="container">
                    <div class="slider-content">
                        <h2 class="slider-title">{{ $title }}</h2>
                        <a href="{{ $route }}" class="btn-play">
                            <i class="fas fa-play"></i> Assistir Agora
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="slider-indicators">
            @foreach($sliders as $index => $slider)
                <div class="dot {{ $index === 0 ? 'active' : '' }}" onclick="goToSlide({{ $index }})"></div>
            @endforeach
        </div>
    </div>

    <!-- Dynamic Sections -->
    <div class="container">
        @foreach($sections as $section)
            @php
                $items = $section->resolveItems();
                if ($items->isEmpty()) continue;
            @endphp
            <section class="section">
                <div class="section-header">
                    <h2 class="section-title">{{ $section->title }}</h2>
                    <a href="{{ route('frontend.search', ['section' => $section->id]) }}" class="section-view-all">Ver Tudo</a>
                </div>
                <div class="horizontal-scroll">
                    @foreach($items as $item)
                        @include('components.movie-card', ['content' => $item])
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

@endsection

@section('scripts')
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slider-item');
        const dots = document.querySelectorAll('.dot');
        
        function showSlide(index) {
            if(!slides.length) return;
            slides.forEach(s => s.classList.remove('active'));
            dots.forEach(d => d.classList.remove('active'));
            
            slides[index].classList.add('active');
            dots[index].classList.add('active');
            currentSlide = index;
        }

        function goToSlide(index) {
            showSlide(index);
        }

        function nextSlide() {
            if(!slides.length) return;
            let next = (currentSlide + 1) % slides.length;
            showSlide(next);
        }

        setInterval(nextSlide, 8000);
    </script>
@endsection

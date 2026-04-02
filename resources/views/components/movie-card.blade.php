@props(['content'])

@php
    $type = $content->type ?? (isset($content->name) ? 'series' : 'movie');
    $route = $type === 'movie' ? route('frontend.movie', $content->slug) : route('frontend.serie', $content->slug);
    $image = $content->poster_path ? 'https://image.tmdb.org/t/p/w500' . $content->poster_path : asset('img/no-poster.jpg');
    $title = $content->title ?? $content->name;
    $year = $content->release_year ?? $content->first_air_year ?? '';
@endphp

<a href="{{ $route }}" class="movie-card">
    <div class="card-rating">
        <i class="fas fa-star" style="color: gold;"></i> {{ number_format($content->rating, 1) }}
    </div>
    <img src="{{ $image }}" alt="{{ $title }}" class="card-image" loading="lazy">
    <div class="card-info">
        <h3 class="card-title">{{ $title }}</h3>
        <span class="card-year">{{ $year }}</span>
    </div>
</a>

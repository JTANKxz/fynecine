@php
    $title = $content->title ?? $content->name;
    $rating = number_format($content->rating, 1);
    $year = $content->release_year ?? $content->first_air_year;
    $image = $content->poster_path ? 'https://image.tmdb.org/t/p/w342' . $content->poster_path : asset('img/no-poster.jpg');
    $url = $content->getTypeAttribute() === 'movie' ? route('frontend.movie', $content->slug) : route('frontend.serie', $content->slug);
@endphp

<div class="movie-card">
    <a href="{{ $url }}" title="Assistir {{ $title }} Online">
        <div class="card-img-wrapper">
            <img src="{{ $image }}" alt="Assistir {{ $title }} Online Grátis Dublado" class="card-img" loading="lazy">
        </div>
        <div class="card-info">
            <span class="card-title">{{ $title }}</span>
            <div class="card-meta">
                <span>{{ $year }}</span>
                @if($rating > 0)
                    <span style="float: right;"><i class="fas fa-star" style="color: gold; font-size: 0.7rem;"></i> {{ $rating }}</span>
                @endif
            </div>
        </div>
    </a>
</div>

@foreach($results as $content)
    @php
        $isMovie = isset($content->title);
        $title = $isMovie ? $content->title : $content->name;
        $image = $content->poster_path ? 'https://image.tmdb.org/t/p/w500' . $content->poster_path : asset('img/no-poster.jpg');
        $url = $isMovie ? route('frontend.movie', $content->slug) : route('frontend.serie', $content->slug);
        $nota = $content->vote_average ?? ($content->rating ?? 0);
        $ano = $isMovie ? ($content->release_date ? date('Y', strtotime($content->release_date)) : $content->release_year) : ($content->first_air_date ? date('Y', strtotime($content->first_air_date)) : $content->first_air_year);
        $duracao = $isMovie ? ($content->runtime ?? 0) : 0;
        $sinopse = $content->overview ?? 'Sinopse não disponível.';
    @endphp
    <div class="card catalog-card" 
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

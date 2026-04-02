@extends('layouts.app')

@section('content')
    <div class="container" style="margin-top: 100px;">
        <h1 style="margin-bottom: 2rem;">Resultados para: "{{ $query }}"</h1>

        @if($movies->isEmpty() && $series->isEmpty())
            <div style="text-align: center; padding: 5rem 0;">
                <i class="fas fa-search" style="font-size: 4rem; color: var(--glass-border); margin-bottom: 2rem;"></i>
                <h2>Nenhum resultado encontrado</h2>
                <p style="color: var(--text-muted);">Tente buscar por termos diferentes ou confira os lançamentos na home.</p>
                <a href="{{ route('home') }}" class="btn-play" style="margin-top: 2rem;">Voltar para o Início</a>
            </div>
        @else
            @if($movies->isNotEmpty())
                <section class="section">
                    <h2 class="section-title">Filmes</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1.5rem;">
                        @foreach($movies as $movie)
                            @include('components.movie-card', ['content' => $movie])
                        @endforeach
                    </div>
                </section>
            @endif

            @if($series->isNotEmpty())
                <section class="section">
                    <h2 class="section-title">Séries</h2>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1.5rem;">
                        @foreach($series as $serie)
                            @include('components.movie-card', ['content' => $serie])
                        @endforeach
                    </div>
                </section>
            @endif
        @endif
    </div>
@endsection

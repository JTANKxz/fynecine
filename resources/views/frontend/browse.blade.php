@extends('layouts.app')

@section('content')
    <div class="container" style="margin-top: 100px;">
        <h1 style="margin-bottom: 0.5rem;">{{ $title }}</h1>
        <p style="color: var(--text-muted); margin-bottom: 3rem;">{{ $description }}</p>

        @if($movies->isEmpty() && $series->isEmpty())
            <div style="text-align: center; padding: 5rem 0;">
                <i class="fas fa-film" style="font-size: 4rem; color: var(--glass-border); margin-bottom: 2rem;"></i>
                <h2>Nenhum conteúdo encontrado</h2>
                <p style="color: var(--text-muted);">Ainda não temos títulos nesta categoria.</p>
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
                <div style="margin-bottom: 2rem;">
                    {{ $movies->links() }}
                </div>
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
                <div style="margin-bottom: 2rem;">
                    {{ $series->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection

<style>
    /* Pagination Styling */
    .pagination {
        display: flex;
        gap: 0.5rem;
        list-style: none;
        justify-content: center;
        margin-top: 2rem;
    }
    .page-item .page-link {
        padding: 0.5rem 1rem;
        background: var(--bg-card);
        border: 1px solid var(--glass-border);
        border-radius: 6px;
        color: white;
    }
    .page-item.active .page-link {
        background: var(--primary-purple);
        border-color: var(--primary-purple);
    }
</style>

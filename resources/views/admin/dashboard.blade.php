@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- WELCOME HEADER -->
<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-white">Bem-vindo ao Painel</h1>
    <p class="text-neutral-400 mt-1">Visão geral do seu catálogo e atividades.</p>
</div>

<!-- STATS GRID -->
<section class="mb-8">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
        <div class="bg-neutral-900 p-4 rounded-xl hover:bg-neutral-800/80 transition border border-neutral-800 group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-600/20 flex items-center justify-center text-red-400 group-hover:scale-110 transition">
                    <i class="fa-solid fa-film"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-white">{{ number_format($stats['movies']) }}</p>
                    <p class="text-xs text-neutral-500 uppercase font-bold">Filmes</p>
                </div>
            </div>
        </div>
        <div class="bg-neutral-900 p-4 rounded-xl hover:bg-neutral-800/80 transition border border-neutral-800 group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-600/20 flex items-center justify-center text-blue-400 group-hover:scale-110 transition">
                    <i class="fa-solid fa-tv"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-white">{{ number_format($stats['series']) }}</p>
                    <p class="text-xs text-neutral-500 uppercase font-bold">Séries</p>
                </div>
            </div>
        </div>
        <div class="bg-neutral-900 p-4 rounded-xl hover:bg-neutral-800/80 transition border border-neutral-800 group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-600/20 flex items-center justify-center text-green-400 group-hover:scale-110 transition">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-white">{{ number_format($stats['users']) }}</p>
                    <p class="text-xs text-neutral-500 uppercase font-bold">Usuários</p>
                </div>
            </div>
        </div>
        <div class="bg-neutral-900 p-4 rounded-xl hover:bg-neutral-800/80 transition border border-neutral-800 group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-yellow-600/20 flex items-center justify-center text-yellow-400 group-hover:scale-110 transition">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-white">{{ number_format($stats['premium_users']) }}</p>
                    <p class="text-xs text-neutral-500 uppercase font-bold">Premium</p>
                </div>
            </div>
        </div>
        <div class="bg-neutral-900 p-4 rounded-xl hover:bg-neutral-800/80 transition border border-neutral-800 group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-600/20 flex items-center justify-center text-purple-400 group-hover:scale-110 transition">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-white">{{ number_format($stats['views_today']) }}</p>
                    <p class="text-xs text-neutral-500 uppercase font-bold">Views Hoje</p>
                </div>
            </div>
        </div>
        <div class="bg-neutral-900 p-4 rounded-xl hover:bg-neutral-800/80 transition border border-neutral-800 group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-pink-600/20 flex items-center justify-center text-pink-400 group-hover:scale-110 transition">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-white">{{ number_format($stats['views_week']) }}</p>
                    <p class="text-xs text-neutral-500 uppercase font-bold">Views Semana</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECONDARY STATS -->
<section class="mb-8">
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
        <div class="bg-neutral-900/50 p-3 rounded-lg border border-neutral-800/50 text-center">
            <p class="text-lg font-bold text-white">{{ number_format($stats['movie_links']) }}</p>
            <p class="text-[10px] text-neutral-500 uppercase font-bold">Links Filmes</p>
        </div>
        <div class="bg-neutral-900/50 p-3 rounded-lg border border-neutral-800/50 text-center">
            <p class="text-lg font-bold text-white">{{ number_format($stats['episode_links']) }}</p>
            <p class="text-[10px] text-neutral-500 uppercase font-bold">Links Episódios</p>
        </div>
        <div class="bg-neutral-900/50 p-3 rounded-lg border border-neutral-800/50 text-center">
            <p class="text-lg font-bold text-white">{{ $stats['channels'] }}</p>
            <p class="text-[10px] text-neutral-500 uppercase font-bold">Canais TV</p>
        </div>
        <div class="bg-neutral-900/50 p-3 rounded-lg border border-neutral-800/50 text-center">
            <p class="text-lg font-bold text-white">{{ $stats['events'] }}</p>
            <p class="text-[10px] text-neutral-500 uppercase font-bold">Eventos</p>
        </div>
        <div class="bg-neutral-900/50 p-3 rounded-lg border border-neutral-800/50 text-center">
            <p class="text-lg font-bold text-white">{{ $stats['teams'] }}</p>
            <p class="text-[10px] text-neutral-500 uppercase font-bold">Times</p>
        </div>
        <div class="bg-neutral-900/50 p-3 rounded-lg border border-neutral-800/50 text-center">
            <p class="text-lg font-bold text-white {{ $stats['requests'] > 0 ? 'text-yellow-400' : '' }}">{{ $stats['requests'] }}</p>
            <p class="text-[10px] text-neutral-500 uppercase font-bold">Pedidos Pendentes</p>
        </div>
    </div>
</section>

<!-- CATEGORIES OVERVIEW -->
@if($categories->isNotEmpty())
<section class="mb-8">
    <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
        <i class="fa-solid fa-folder-tree text-netflix"></i> Categorias
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-3">
        @foreach($categories as $cat)
        <a href="{{ route('admin.sections.index', ['category_id' => $cat->id]) }}" 
           class="bg-neutral-900 rounded-xl p-4 border border-neutral-800 hover:border-netflix/50 transition group">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-netflix/20 flex items-center justify-center text-netflix text-sm">
                    <i class="fa-solid fa-{{ $cat->icon ?? 'layer-group' }}"></i>
                </div>
                <div class="min-w-0">
                    <p class="font-bold text-sm text-white truncate">{{ $cat->name }}</p>
                    <p class="text-[10px] text-neutral-500">{{ $cat->movies_count }} filmes • {{ $cat->series_count }} séries</p>
                </div>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

<div class="grid lg:grid-cols-3 gap-6 mb-8">
    <!-- TOP 5 MAIS ASSISTIDOS -->
    <section class="lg:col-span-1">
        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
            <i class="fa-solid fa-fire text-orange-500"></i> Top 5 da Semana
        </h2>
        <div class="bg-neutral-900 rounded-xl border border-neutral-800 overflow-hidden">
            @forelse($topMovies as $index => $movie)
            <div class="flex items-center gap-3 p-3 hover:bg-neutral-800/50 transition {{ !$loop->last ? 'border-b border-neutral-800' : '' }}">
                <span class="text-2xl font-extrabold text-neutral-700 w-8 text-center">{{ $index + 1 }}</span>
                <img src="{{ $movie->poster_path }}" alt="{{ $movie->title }}" class="w-10 h-14 object-cover rounded">
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-sm text-white truncate">{{ $movie->title }}</p>
                    <p class="text-xs text-neutral-500">{{ $movie->views_count }} views</p>
                </div>
            </div>
            @empty
            <div class="p-6 text-center text-neutral-500 text-sm">
                <i class="fa-solid fa-chart-simple text-2xl mb-2 block"></i>
                Sem dados de visualização ainda.
            </div>
            @endforelse
        </div>
    </section>

    <!-- ÚLTIMOS FILMES -->
    <section class="lg:col-span-1">
        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
            <i class="fa-solid fa-film text-red-400"></i> Últimos Filmes
        </h2>
        <div class="bg-neutral-900 rounded-xl border border-neutral-800 overflow-hidden">
            @foreach($recentMovies as $movie)
            <div class="flex items-center gap-3 p-3 hover:bg-neutral-800/50 transition {{ !$loop->last ? 'border-b border-neutral-800' : '' }}">
                <img src="{{ $movie->poster_path }}" alt="{{ $movie->title }}" class="w-10 h-14 object-cover rounded">
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-sm text-white truncate">{{ $movie->title }}</p>
                    <p class="text-xs text-neutral-500">{{ $movie->release_year }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- ÚLTIMAS SÉRIES -->
    <section class="lg:col-span-1">
        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
            <i class="fa-solid fa-tv text-blue-400"></i> Últimas Séries
        </h2>
        <div class="bg-neutral-900 rounded-xl border border-neutral-800 overflow-hidden">
            @foreach($recentSeries as $serie)
            <div class="flex items-center gap-3 p-3 hover:bg-neutral-800/50 transition {{ !$loop->last ? 'border-b border-neutral-800' : '' }}">
                <img src="{{ $serie->poster_path }}" alt="{{ $serie->name }}" class="w-10 h-14 object-cover rounded">
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-sm text-white truncate">{{ $serie->name }}</p>
                    <p class="text-xs text-neutral-500">{{ $serie->first_air_year }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>
</div>

<div class="grid lg:grid-cols-2 gap-6 mb-8">
    <!-- ÚLTIMOS USUÁRIOS -->
    <section>
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-lg font-bold flex items-center gap-2">
                <i class="fa-solid fa-users text-green-400"></i> Últimos Usuários
            </h2>
            <a href="{{ route('admin.users.index') }}" class="text-netflix text-xs hover:underline font-bold">Ver todos →</a>
        </div>
        <div class="bg-neutral-900 rounded-xl border border-neutral-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm" style="min-width: 400px">
                    <thead class="bg-neutral-800/50">
                        <tr>
                            <th class="p-3 text-xs text-neutral-400 uppercase">Usuário</th>
                            <th class="p-3 text-xs text-neutral-400 uppercase">Plano</th>
                            <th class="p-3 text-right text-xs text-neutral-400 uppercase">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800/50">
                        @foreach($recentUsers as $user)
                        <tr class="hover:bg-neutral-800/30">
                            <td class="p-3">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=8B2FFF&color=fff' }}" class="w-7 h-7 rounded-full">
                                    <div>
                                        <p class="font-bold text-white text-xs">{{ $user->name }}</p>
                                        <p class="text-[10px] text-neutral-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $user->isPremium() ? 'bg-yellow-600/20 text-yellow-400' : 'bg-neutral-700/50 text-neutral-500' }}">
                                    {{ strtoupper($user->plan_type ?? 'FREE') }}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <a href="{{ route('admin.users.index', ['search' => $user->id]) }}" class="text-blue-500 hover:text-blue-400 text-xs">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- AÇÕES RÁPIDAS -->
    <section>
        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
            <i class="fa-solid fa-bolt text-yellow-400"></i> Ações Rápidas
        </h2>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('admin.tmdb') }}" class="bg-neutral-900 p-5 rounded-xl hover:bg-neutral-800 transition group border border-neutral-800 hover:border-netflix/50">
                <i class="fa-solid fa-cloud-arrow-down text-xl text-netflix mb-2 group-hover:scale-110 transition block"></i>
                <h3 class="font-bold text-sm">Importar TMDB</h3>
                <p class="text-[10px] text-neutral-500 mt-0.5">Filmes e séries</p>
            </a>
            <a href="{{ route('admin.events.create') }}" class="bg-neutral-900 p-5 rounded-xl hover:bg-neutral-800 transition group border border-neutral-800 hover:border-netflix/50">
                <i class="fa-solid fa-trophy text-xl text-orange-500 mb-2 group-hover:scale-110 transition block"></i>
                <h3 class="font-bold text-sm">Novo Evento</h3>
                <p class="text-[10px] text-neutral-500 mt-0.5">Criar evento ao vivo</p>
            </a>
            <a href="{{ route('admin.requests.index') }}" class="bg-neutral-900 p-5 rounded-xl hover:bg-neutral-800 transition group border border-neutral-800 hover:border-netflix/50">
                <i class="fa-solid fa-hand-holding-heart text-xl text-yellow-500 mb-2 group-hover:scale-110 transition block"></i>
                <h3 class="font-bold text-sm">Pedidos</h3>
                <p class="text-[10px] text-neutral-500 mt-0.5">{{ $stats['requests'] }} pendentes</p>
            </a>
            <a href="{{ route('admin.settings.edit') }}" class="bg-neutral-900 p-5 rounded-xl hover:bg-neutral-800 transition group border border-neutral-800 hover:border-netflix/50">
                <i class="fa-solid fa-gear text-xl text-neutral-400 mb-2 group-hover:scale-110 transition block"></i>
                <h3 class="font-bold text-sm">Configurações</h3>
                <p class="text-[10px] text-neutral-500 mt-0.5">Ajustes do App</p>
            </a>
            <a href="{{ route('admin.teams.index') }}" class="bg-neutral-900 p-5 rounded-xl hover:bg-neutral-800 transition group border border-neutral-800 hover:border-netflix/50">
                <i class="fa-solid fa-shield-halved text-xl text-green-500 mb-2 group-hover:scale-110 transition block"></i>
                <h3 class="font-bold text-sm">Times</h3>
                <p class="text-[10px] text-neutral-500 mt-0.5">{{ $stats['teams'] }} cadastrados</p>
            </a>
            <a href="{{ route('admin.sections.index') }}" class="bg-neutral-900 p-5 rounded-xl hover:bg-neutral-800 transition group border border-neutral-800 hover:border-netflix/50">
                <i class="fa-solid fa-wand-magic-sparkles text-xl text-purple-500 mb-2 group-hover:scale-110 transition block"></i>
                <h3 class="font-bold text-sm">Páginas</h3>
                <p class="text-[10px] text-neutral-500 mt-0.5">Customizar Home</p>
            </a>
        </div>
    </section>
</div>

<!-- NETWORKS -->
<section class="mb-8">
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-lg font-bold flex items-center gap-2">
            <i class="fa-solid fa-tower-broadcast text-cyan-400"></i> Networks / Plataformas
        </h2>
        <a href="{{ route('admin.networks.index') }}" class="text-netflix text-xs hover:underline font-bold">Gerenciar →</a>
    </div>
    <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3">
        @foreach($networks as $network)
        <a href="{{ route('admin.networks.content', $network) }}" class="bg-neutral-900 aspect-square rounded-xl flex flex-col items-center justify-center p-3 hover:bg-neutral-800 transition border border-neutral-800 hover:border-netflix/30 group">
            <div class="w-10 h-10 mb-1 flex items-center justify-center overflow-hidden rounded">
                @if($network->image_url)
                    <img src="{{ $network->image_url }}" alt="{{ $network->name }}" class="w-full h-full object-contain group-hover:scale-110 transition">
                @else
                    <i class="fa-solid fa-layer-group text-xl text-neutral-600"></i>
                @endif
            </div>
            <span class="text-[9px] uppercase font-bold text-neutral-500 text-center truncate w-full">{{ $network->name }}</span>
        </a>
        @endforeach
    </div>
</section>
@endsection
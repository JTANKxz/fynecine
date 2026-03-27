@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- STATS -->
<section>
    <div class="grid md:grid-cols-4 gap-4 mb-8">
        <div class="bg-neutral-900 p-5 rounded hover:bg-neutral-800 transition border-l-4 border-netflix">
            <p class="text-neutral-400 text-sm uppercase font-bold">Filmes</p>
            <p class="text-3xl font-bold text-white">{{ $stats['movies'] }}</p>
        </div>
        <div class="bg-neutral-900 p-5 rounded hover:bg-neutral-800 transition border-l-4 border-blue-600">
            <p class="text-neutral-400 text-sm uppercase font-bold">Séries</p>
            <p class="text-3xl font-bold text-white">{{ $stats['series'] }}</p>
        </div>
        <div class="bg-neutral-900 p-5 rounded hover:bg-neutral-800 transition border-l-4 border-green-600">
            <p class="text-neutral-400 text-sm uppercase font-bold">Usuários</p>
            <p class="text-3xl font-bold text-white">{{ $stats['users'] }}</p>
        </div>
        <div class="bg-neutral-900 p-5 rounded hover:bg-neutral-800 transition border-l-4 border-yellow-600">
            <p class="text-neutral-400 text-sm uppercase font-bold">Pedidos Pendentes</p>
            <p class="text-3xl font-bold text-white">{{ $stats['requests'] }}</p>
        </div>
        <div class="bg-neutral-900 p-5 rounded hover:bg-neutral-800 transition border-l-4 border-purple-600">
            <p class="text-neutral-400 text-sm uppercase font-bold">Links de Filmes</p>
            <p class="text-3xl font-bold text-white">{{ $stats['movie_links'] }}</p>
        </div>
        <div class="bg-neutral-900 p-5 rounded hover:bg-neutral-800 transition border-l-4 border-pink-600">
            <p class="text-neutral-400 text-sm uppercase font-bold">Links de Episódios</p>
            <p class="text-3xl font-bold text-white">{{ $stats['episode_links'] }}</p>
        </div>
    </div>
</section>

<div class="grid md:grid-cols-2 gap-8">
    <!-- ÚLTIMOS USUÁRIOS -->
    <section>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Últimos Usuários</h2>
            <a href="{{ route('admin.users.index') }}" class="text-netflix text-sm hover:underline">Ver todos</a>
        </div>
        <div class="bg-neutral-900 rounded-lg overflow-hidden">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-800">
                    <tr>
                        <th class="p-4">Usuário</th>
                        <th class="p-4">Plano</th>
                        <th class="p-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800">
                    @foreach($recentUsers as $user)
                    <tr class="hover:bg-neutral-800/50">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}" class="w-8 h-8 rounded-full">
                                <div>
                                    <p class="font-bold text-white">{{ $user->name }}</p>
                                    <p class="text-xs text-neutral-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            <span class="px-2 py-0.5 rounded text-xs {{ $user->isPremium() ? 'bg-yellow-600/20 text-yellow-500' : 'bg-neutral-700 text-neutral-400' }}">
                                {{ strtoupper($user->plan_type ?? 'free') }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                             <a href="{{ route('admin.users.index', ['search' => $user->id]) }}" class="text-blue-500 hover:text-blue-400">
                                <i class="fa-solid fa-eye"></i>
                             </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- ATALHOS RÁPIDOS -->
    <section>
        <h2 class="text-xl font-bold mb-4">Ações Rápidas</h2>
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('admin.movies.index') }}" class="bg-neutral-900 p-6 rounded hover:bg-neutral-800 transition group border border-neutral-800">
                <i class="fa-solid fa-film text-2xl text-netflix mb-3 group-hover:scale-110 transition"></i>
                <h3 class="font-bold">Filmes</h3>
                <p class="text-xs text-neutral-500">Gerenciar catálogo</p>
            </a>
            <a href="{{ route('admin.series.index') }}" class="bg-neutral-900 p-6 rounded hover:bg-neutral-800 transition group border border-neutral-800">
                <i class="fa-solid fa-tv text-2xl text-blue-500 mb-3 group-hover:scale-110 transition"></i>
                <h3 class="font-bold">Séries</h3>
                <p class="text-xs text-neutral-500">Episódios e temporadas</p>
            </a>
            <a href="{{ route('admin.requests.index') }}" class="bg-neutral-900 p-6 rounded hover:bg-neutral-800 transition group border border-neutral-800">
                <i class="fa-solid fa-hand-holding-heart text-2xl text-yellow-500 mb-3 group-hover:scale-110 transition"></i>
                <h3 class="font-bold">Pedidos</h3>
                <p class="text-xs text-neutral-500">{{ $stats['requests'] }} pendentes</p>
            </a>
            <a href="{{ route('admin.settings.edit') }}" class="bg-neutral-900 p-6 rounded hover:bg-neutral-800 transition group border border-neutral-800">
                <i class="fa-solid fa-gear text-2xl text-neutral-400 mb-3 group-hover:scale-110 transition"></i>
                <h3 class="font-bold">Configurações</h3>
                <p class="text-xs text-neutral-500">Ajustes do App</p>
            </a>
        </div>
    </section>
</div>
@endsection
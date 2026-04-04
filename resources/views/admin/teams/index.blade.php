@extends('layouts.admin')

@section('title', 'Times / Equipes')

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <h2 class="text-2xl font-bold">Times / Equipes</h2>
    <a href="{{ route('admin.teams.create') }}" class="bg-netflix hover:bg-purple-700 px-4 py-2 rounded text-sm font-bold transition">
        <i class="fa-solid fa-plus mr-1"></i> Novo Time
    </a>
</div>

@if(session('success'))
    <div class="bg-green-900/30 border border-green-700 text-green-400 p-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif

@if($teams->isEmpty())
    <div class="bg-neutral-900 rounded-lg p-12 text-center">
        <i class="fa-solid fa-shield-halved text-4xl text-neutral-600 mb-4"></i>
        <p class="text-neutral-400">Nenhum time cadastrado.</p>
        <a href="{{ route('admin.teams.create') }}" class="text-netflix hover:underline text-sm mt-2 inline-block">Cadastrar primeiro time</a>
    </div>
@else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @foreach($teams as $team)
        <div class="bg-neutral-900 rounded-lg border border-neutral-800 overflow-hidden hover:border-netflix/50 transition group relative">
            <div class="aspect-square flex items-center justify-center bg-neutral-800/50 p-4">
                @if($team->image_url)
                    <img src="{{ $team->image_url }}" alt="{{ $team->name }}" class="w-full h-full object-contain group-hover:scale-105 transition">
                @else
                    <i class="fa-solid fa-shield-halved text-4xl text-neutral-600"></i>
                @endif
            </div>
            <div class="p-3 text-center">
                <p class="font-bold text-sm truncate">{{ $team->name }}</p>
            </div>
            <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition flex gap-1">
                <a href="{{ route('admin.teams.edit', $team) }}" class="w-7 h-7 bg-blue-600/80 rounded flex items-center justify-center text-xs hover:bg-blue-500">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <form method="POST" action="{{ route('admin.teams.destroy', $team) }}" onsubmit="return confirm('Remover este time?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-7 h-7 bg-red-600/80 rounded flex items-center justify-center text-xs hover:bg-red-500">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $teams->links() }}
    </div>
@endif
@endsection

@extends('layouts.admin')

@section('title', 'Shorts')

@section('content')
<section>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div><h2 class="text-2xl font-bold">Shorts</h2><p class="text-sm text-neutral-500">Feed vertical com fontes diretas e players oficiais incorporados.</p></div>
        <a href="{{ route('admin.shorts.create') }}" class="bg-netflix px-5 py-3 rounded-xl font-bold"><i class="fa-solid fa-plus mr-2"></i>Novo Short</a>
    </div>
    @if(session('success'))<div class="mb-5 p-4 rounded-xl border border-green-600/40 text-green-400 bg-green-950/30">{{ session('success') }}</div>@endif
    <form class="mb-5 flex gap-2" method="GET"><input name="search" value="{{ request('search') }}" placeholder="Buscar título" class="bg-neutral-900 border border-neutral-700 rounded-xl px-4 py-2 w-full md:max-w-sm"><button class="border border-neutral-700 px-4 rounded-xl">Buscar</button></form>
    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl overflow-x-auto">
        <table class="w-full text-sm"><thead class="text-neutral-400 text-xs bg-neutral-800/50"><tr><th class="p-4 text-left">SHORT</th><th class="p-4 text-left">FONTE</th><th class="p-4 text-left">STATUS</th><th class="p-4 text-right">AÇÕES</th></tr></thead><tbody class="divide-y divide-neutral-800">
        @forelse($shorts as $short)<tr><td class="p-4 flex gap-3 items-center">@if($short->thumbnail_url)<img src="{{ $short->thumbnail_url }}" class="w-10 h-14 object-cover rounded">@endif<div><div class="font-bold">{{ $short->title }}</div><div class="text-xs text-neutral-500">{{ $short->category ?: 'Sem categoria' }}</div></div></td><td class="p-4 uppercase text-xs">{{ $short->source_provider }}<div class="text-neutral-500 normal-case mt-1">{{ $short->playback_kind }}</div></td><td class="p-4"><span class="text-xs {{ $short->is_active ? 'text-green-400' : 'text-neutral-500' }}">{{ $short->is_active ? 'Ativo' : 'Inativo' }}</span><div class="text-xs text-neutral-500">{{ $short->availability }}</div></td><td class="p-4 text-right"><a class="text-netflix mr-3" href="{{ route('admin.shorts.edit', $short) }}"><i class="fa-solid fa-pen"></i></a><form class="inline" method="POST" action="{{ route('admin.shorts.destroy', $short) }}" onsubmit="return confirm('Remover este Short?')">@csrf @method('DELETE')<button class="text-red-400"><i class="fa-solid fa-trash"></i></button></form></td></tr>
        @empty<tr><td colspan="4" class="text-center text-neutral-500 p-10">Nenhum Short cadastrado.</td></tr>@endforelse
        </tbody></table>
    </div><div class="mt-6">{{ $shorts->links() }}</div>
</section>
@endsection

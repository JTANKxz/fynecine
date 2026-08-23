@extends('layouts.admin')

@section('title', 'Modo Adulto')

@section('content')
<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.22em] text-red-400">Área restrita</p>
        <h2 class="mt-1 text-2xl font-bold text-white">Modo adulto</h2>
        <p class="mt-1 text-sm text-neutral-400">Gerencie o catálogo, sua publicação e os conteúdos que estarão disponíveis aos perfis autorizados.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.settings.edit') }}#adult-mode" class="inline-flex items-center gap-2 rounded-lg border border-neutral-700 bg-neutral-900 px-4 py-2 text-sm font-bold text-white transition hover:border-red-500">
            <i class="fa-solid fa-gear"></i> Configuração global
        </a>
        <a href="{{ route('admin.adult.galleries.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-netflix px-4 py-2 text-sm font-bold text-white transition hover:bg-red-700">
            <i class="fa-solid fa-plus"></i> Nova galeria
        </a>
    </div>
</div>

<div class="mb-6 rounded-xl border {{ $config->is_adult_active ? 'border-emerald-500/30 bg-emerald-500/10' : 'border-red-500/30 bg-red-500/10' }} p-4">
    <div class="flex items-start gap-3">
        <i class="fa-solid {{ $config->is_adult_active ? 'fa-circle-check text-emerald-400' : 'fa-circle-xmark text-red-400' }} mt-0.5"></i>
        <div><p class="font-bold text-white">{{ $config->is_adult_active ? 'Modo adulto ativo' : 'Modo adulto desativado' }}</p><p class="mt-1 text-sm text-neutral-300">{{ $config->is_adult_active ? 'A API exige perfil autorizado e confirmação recente do PIN adulto.' : 'Nenhum perfil consegue acessar a API adulta enquanto estiver desativado.' }}</p></div>
    </div>
</div>

<div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
    @foreach ([['Galerias', $stats['galleries'], 'fa-photo-film'], ['Mídias', $stats['media'], 'fa-images'], ['Modelos', $stats['models'], 'fa-person-dress'], ['Categorias', $stats['categories'], 'fa-tags'], ['Inativos', $stats['inactive'], 'fa-eye-slash']] as [$label, $value, $icon])
        <div class="rounded-xl border border-neutral-800 bg-neutral-900 p-4"><i class="fa-solid {{ $icon }} text-red-400"></i><p class="mt-3 text-2xl font-bold text-white">{{ $value }}</p><p class="text-xs text-neutral-400">{{ $label }}</p></div>
    @endforeach
</div>

<div class="mt-8 rounded-xl border border-neutral-800 bg-neutral-900">
    <div class="flex items-center justify-between border-b border-neutral-800 p-4"><div><h3 class="font-bold text-white">Últimas galerias</h3><p class="text-xs text-neutral-500">Prévia rápida do catálogo publicado.</p></div><a href="{{ route('admin.adult.galleries.index') }}" class="text-sm font-bold text-red-400 hover:text-red-300">Ver todas</a></div>
    <div class="grid grid-cols-2 gap-4 p-4 sm:grid-cols-3 xl:grid-cols-6">
        @forelse($recentGalleries as $gallery)
            <a href="{{ route('admin.adult.galleries.edit', $gallery) }}" class="group overflow-hidden rounded-lg border border-neutral-800 bg-black"><img class="aspect-[2/3] w-full object-cover transition group-hover:scale-105" src="{{ $gallery->cover_url ?: 'https://via.placeholder.com/300x450?text=Sem+Capa' }}" alt=""><div class="p-2"><p class="truncate text-xs font-bold text-white">{{ $gallery->title }}</p><p class="mt-1 text-[10px] {{ $gallery->is_active ? 'text-emerald-400' : 'text-red-400' }}">{{ $gallery->is_active ? 'Ativa' : 'Inativa' }}</p></div></a>
        @empty
            <p class="col-span-full py-10 text-center text-sm text-neutral-500">Nenhuma galeria cadastrada.</p>
        @endforelse
    </div>
</div>
@endsection

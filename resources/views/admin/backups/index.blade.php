@extends('layouts.admin')

@section('title', 'Backups do banco de dados')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div>
        <p class="text-sm text-neutral-400">Administração / Segurança</p>
        <h2 class="text-2xl font-bold text-white">Backup do banco de dados</h2>
        <p class="text-sm text-neutral-400 mt-1">Exporte uma cópia SQL completa ou apenas os módulos que precisa migrar.</p>
    </div>
    <div class="rounded-xl border border-neutral-800 bg-neutral-900 p-5">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0"><i class="fa-solid fa-database"></i></div>
            <div>
                <h3 class="font-semibold text-white">Backup completo</h3>
                <p class="text-sm text-neutral-400 mt-1">Exporta todas as tabelas existentes, estrutura, registros e relacionamentos do banco conectado à aplicação.</p>
                <a href="{{ route('admin.backups.download', 'full') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-red-600 hover:bg-red-500 px-4 py-2.5 text-sm font-semibold text-white"><i class="fa-solid fa-download"></i> Baixar backup completo (.sql)</a>
            </div>
        </div>
    </div>
    <div>
        <h3 class="text-lg font-semibold text-white mb-3">Backups por módulo</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($groups as $key => $group)
                <div class="rounded-xl border border-neutral-800 bg-neutral-900 p-5 flex flex-col">
                    <div class="w-10 h-10 rounded-lg bg-neutral-800 text-neutral-200 flex items-center justify-center mb-3">
                        @if($key === 'movies') <i class="fa-solid fa-film"></i>
                        @elseif($key === 'series') <i class="fa-solid fa-tv"></i>
                        @elseif($key === 'seasons') <i class="fa-solid fa-layer-group"></i>
                        @elseif($key === 'episodes') <i class="fa-solid fa-play"></i>
                        @else <i class="fa-solid fa-users"></i> @endif
                    </div>
                    <h4 class="font-semibold text-white">{{ $group['label'] }}</h4>
                    <p class="text-sm text-neutral-400 mt-2 flex-1">{{ $group['description'] }}</p>
                    <a href="{{ route('admin.backups.download', $key) }}" class="mt-5 inline-flex justify-center items-center gap-2 rounded-lg border border-neutral-700 hover:bg-neutral-800 px-4 py-2.5 text-sm font-medium text-white"><i class="fa-solid fa-file-arrow-down"></i> Baixar módulo (.sql)</a>
                </div>
            @endforeach
        </div>
    </div>
    <div class="rounded-xl border border-amber-900/60 bg-amber-950/20 p-4 text-sm text-amber-200">
        <div class="flex gap-2 font-semibold"><i class="fa-solid fa-triangle-exclamation mt-0.5"></i> Cuidados com restauração</div>
        <p class="mt-2 text-amber-200/80">Os arquivos contêm dados reais, inclusive informações pessoais e credenciais protegidas por hash. Guarde-os em local seguro. O SQL inclui DROP TABLE para recriar estruturas; importar em um banco existente substitui tabelas de mesmo nome. Faça backup do destino antes de restaurar.</p>
    </div>
    <p class="text-xs text-neutral-500">Conexão atual: {{ strtoupper($driver) }}. Os backups SQL estão habilitados para MySQL/MariaDB.</p>
</div>
@endsection

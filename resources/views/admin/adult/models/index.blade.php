@extends('layouts.admin')

@section('title', 'Modelos Adultas')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Performers / Modelos</h2>
    <a href="{{ route('admin.adult.models.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-netflix/80 flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Nova Modelo
    </a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    @forelse($models as $model)
    <div class="bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800 flex flex-col group">
        <div class="relative aspect-[3/4] overflow-hidden">
            <img src="{{ $model->photo_url ?? 'https://via.placeholder.com/300x400?text=No+Photo' }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
            <div class="absolute bottom-4 left-4 right-4">
                <h3 class="text-lg font-bold truncate">{{ $model->name }}</h3>
                <div class="flex items-center gap-4 mt-1 text-xs text-neutral-400">
                    @if($model->instagram) <i class="fa-brands fa-instagram"></i> @endif
                    @if($model->twitter) <i class="fa-brands fa-x-twitter"></i> @endif
                    <span class="ml-auto px-2 py-0.5 rounded-full {{ $model->is_active ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                        {{ $model->is_active ? 'Ativa' : 'Inativa' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="p-4 flex justify-between items-center border-t border-neutral-800">
            <a href="{{ route('admin.adult.models.edit', $model->id) }}" class="text-neutral-300 hover:text-netflix text-sm font-medium transition">
                <i class="fa-solid fa-pen-to-square mr-1"></i> Editar
            </a>
            <form action="{{ route('admin.adult.models.destroy', $model->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-neutral-500 hover:text-red-500 transition" onclick="return confirm('Excluir modelo?')">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-full p-20 text-center bg-neutral-900 rounded-lg border border-neutral-800">
        <i class="fa-solid fa-user-slash text-5xl text-neutral-700 mb-4"></i>
        <p class="text-neutral-500 italic">Nenhuma modelo cadastrada ainda.</p>
    </div>
    @endforelse
</div>
@endsection

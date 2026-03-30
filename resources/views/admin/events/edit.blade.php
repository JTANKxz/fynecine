@extends('layouts.admin')

@section('title', 'Editar Evento Ao Vivo')

@section('content')
<section class="max-w-4xl">
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('admin.events.index') }}" class="w-10 h-10 bg-neutral-900 border border-neutral-800 rounded-full flex items-center justify-center hover:bg-neutral-800 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-white">Editar: {{ $event->title }}</h2>
            <p class="text-sm text-neutral-500">Altere os detalhes da transmissão abaixo.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-900/20 border border-red-600 text-red-400 px-4 py-3 rounded text-sm">
            <ul class="list-disc ml-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-6 md:p-8 shadow-2xl">
        <form action="{{ route('admin.events.update', $event->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Título --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Título do Evento</label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">
                </div>

                {{-- Imagem --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">URL da Capa/Banner (Opcional)</label>
                    <input type="url" name="image_url" value="{{ old('image_url', $event->image_url) }}" placeholder="https://..."
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition text-xs font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-neutral-800/30 rounded-xl border border-neutral-800">
                {{-- Time Home --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Time da Casa (Opcional)</label>
                    <input type="text" name="home_team" value="{{ old('home_team', $event->home_team) }}"
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">
                </div>

                {{-- Time Away --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Time Visitante (Opcional)</label>
                    <input type="text" name="away_team" value="{{ old('away_team', $event->away_team) }}"
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Início --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Horário de Início (Fuso SP)</label>
                    <input type="datetime-local" name="start_time" value="{{ old('start_time', $event->start_time->format('Y-m-d\TH:i')) }}" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition text-sm">
                </div>

                {{-- Fim --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Horário de Término (Fuso SP)</label>
                    <input type="datetime-local" name="end_time" value="{{ old('end_time', $event->end_time->format('Y-m-d\TH:i')) }}" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition text-sm">
                </div>
            </div>

            {{-- Descrição --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Descrição (Opcional)</label>
                <textarea name="description" rows="3" placeholder="Detalhes sobre a transmissão..."
                    class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">{{ old('description', $event->description) }}</textarea>
            </div>

            <div class="border-t border-neutral-800 pt-6 flex flex-col md:flex-row gap-8 items-center justify-between">
                {{-- Status --}}
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="is_active" id="is_active" {{ $event->is_active ? 'checked' : '' }} value="1"
                        class="w-5 h-5 accent-netflix rounded bg-black border-neutral-800">
                    <span class="text-white font-bold text-sm group-hover:text-netflix transition text-nowrap">EVENTO ATIVO</span>
                </label>

                <button type="submit" class="w-full md:w-auto bg-netflix hover:bg-red-700 text-white font-black px-10 py-4 rounded-xl shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-save mr-1"></i> ATUALIZAR EVENTO
                </button>
            </div>
        </form>
    </div>
</section>
@endsection

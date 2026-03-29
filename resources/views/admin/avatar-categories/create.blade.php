@extends('layouts.admin')

@section('title', 'Nova Categoria de Avatar')

@section('content')
<section class="max-w-2xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">Nova Categoria de Avatar</h2>
        <p class="text-neutral-400 text-sm">Crie uma nova categoria para organizar seus avatares (Ex: Netflix, Star Wars).</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-900/50 border border-red-600 text-red-200 px-4 py-2 rounded text-sm">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-neutral-900 p-6 rounded-xl border border-neutral-800">
        <form action="{{ route('admin.avatar-categories.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Nome da Categoria</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
            </div>

            <div class="flex gap-2 pt-4">
                <button type="submit" class="bg-netflix px-6 py-2 rounded font-bold hover:bg-red-700 transition">
                    Criar Categoria
                </button>
                <a href="{{ route('admin.avatar-categories.index') }}" class="px-6 py-2 rounded font-bold bg-neutral-800 hover:bg-neutral-700 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</section>
@endsection

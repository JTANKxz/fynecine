@extends('layouts.admin')

@section('title', 'Gerenciar Home Adulta')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold">Personalizar Home Adulta</h2>
        <p class="text-neutral-500 text-sm">Organize como os itens aparecem na seção adulta do app.</p>
    </div>
    <a href="{{ route('admin.adult.home-sections.create') }}" class="bg-netflix px-4 py-2 rounded hover:bg-netflix/80 flex items-center gap-2">
        <i class="fa-solid fa-plus"></i> Nova Seção
    </a>
</div>

<div class="bg-neutral-900 rounded-lg overflow-hidden border border-neutral-800">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-neutral-950 border-b border-neutral-800 text-neutral-400 text-xs uppercase">
                <th class="p-4 w-16">Ordem</th>
                <th class="p-4">Título da Seção</th>
                <th class="p-4">Tipo / Fonte de Dados</th>
                <th class="p-4 text-center">Limite</th>
                <th class="p-4">Status</th>
                <th class="p-4 text-right">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-neutral-800">
            @forelse($sections as $section)
            <tr class="hover:bg-neutral-800/50 transition">
                <td class="p-4 text-neutral-500 text-sm">#{{ $section->order }}</td>
                <td class="p-4 font-bold">{{ $section->title }}</td>
                <td class="p-4">
                    <span class="text-xs px-2 py-1 bg-netflix/10 border border-netflix/30 text-netflix rounded uppercase font-bold tracking-tighter">
                        {{ $section->type }}
                    </span>
                </td>
                <td class="p-4 text-center text-neutral-400 font-mono">{{ $section->limit }}</td>
                <td class="p-4">
                     <span class="px-2 py-1 rounded-full text-[10px] uppercase font-bold {{ $section->is_active ? 'bg-green-600/20 text-green-400 border border-green-600/50' : 'bg-red-600/20 text-red-400 border border-red-600/50' }}">
                        {{ $section->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td class="p-4 text-right space-x-2">
                    <a href="{{ route('admin.adult.home-sections.edit', $section->id) }}" class="text-neutral-400 hover:text-blue-400 transition">
                        <i class="fa-solid fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.adult.home-sections.destroy', $section->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-neutral-600 hover:text-red-500 transition" onclick="return confirm('Excluir esta seção?')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-20 text-center text-neutral-500 italic">Nenhuma seção configurada para a Home Adulta.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

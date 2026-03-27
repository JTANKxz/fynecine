@extends('layouts.admin')

@section('title', 'Planos de Assinatura')

@section('content')
<section class="max-w-6xl">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-white">Planos de Assinatura</h2>
            <p class="text-sm text-neutral-500">Crie e gerencie os planos VIP e seus preços.</p>
        </div>
        <a href="{{ route('admin.subscription-plans.create') }}" class="bg-netflix hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-bold flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> Novo Plano
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-900/20 border border-green-600 text-green-400 px-4 py-3 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-900 border border-neutral-800 rounded-xl overflow-hidden table-container">
        <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-neutral-300">
            <thead class="bg-neutral-950 text-neutral-500 uppercase text-xs border-b border-neutral-800">
                <tr>
                    <th class="px-6 py-4">Nome</th>
                    <th class="px-6 py-4">Nível de Conta</th>
                    <th class="px-6 py-4">Preço</th>
                    <th class="px-6 py-4">Duração</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-800">
                @forelse($plans as $plan)
                <tr class="hover:bg-neutral-800/50 transition">
                    <td class="px-6 py-4 font-bold text-white">{{ $plan->name }}</td>
                    <td class="px-6 py-4">
                        @if($plan->plan_type === 'premium')
                            <span class="bg-amber-500/20 text-amber-500 px-2 py-1 rounded text-xs font-bold uppercase"><i class="fa-solid fa-crown mr-1"></i> Premium</span>
                        @else
                            <span class="bg-blue-500/20 text-blue-500 px-2 py-1 rounded text-xs font-bold uppercase">Basic</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">R$ {{ number_format($plan->price, 2, ',', '.') }}</td>
                    <td class="px-6 py-4">{{ $plan->duration_days }} dias</td>
                    <td class="px-6 py-4">
                        @if($plan->is_active)
                            <span class="text-green-500 bg-green-500/10 px-2 py-1 rounded text-xs"><i class="fa-solid fa-check"></i> Ativo</span>
                        @else
                            <span class="text-neutral-500 bg-neutral-800 px-2 py-1 rounded text-xs">Inativo</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.subscription-plans.edit', $plan) }}" class="text-blue-500 hover:text-blue-400 p-2" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('admin.subscription-plans.destroy', $plan) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja apagar este plano?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-400 p-2" title="Excluir">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-neutral-500">
                        Nenhum plano criado ainda.<br>
                        <a href="{{ route('admin.subscription-plans.create') }}" class="text-netflix hover:underline mt-2 inline-block">Criar meu primeiro plano</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    
    <div class="mt-6">
        {{ $plans->links() }}
    </div>
</section>
@endsection

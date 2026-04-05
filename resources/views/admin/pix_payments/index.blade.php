@extends('layouts.admin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-white"><i class="fa-brands fa-pix text-green-400 mr-2"></i> Vendas PIX</h1>
</div>

<!-- Filtros -->
<div class="bg-neutral-900 border border-neutral-800 rounded-lg p-4 mb-6">
    <form method="GET" action="{{ route('admin.pix-payments.index') }}" class="flex gap-4 items-end">
        <div>
            <label class="block text-sm text-neutral-400 mb-1">Filtrar por Status</label>
            <select name="status" class="w-full bg-neutral-800 border border-neutral-700 rounded p-2 text-white outline-none focus:border-netflix transition-colors">
                <option value="">Todos</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprovados</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendentes</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelados / Expirados</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejeitados</option>
            </select>
        </div>
        <button type="submit" class="bg-netflix hover:bg-red-700 text-white px-6 py-2 rounded font-bold transition-colors">
            Filtrar
        </button>
        @if(request('status'))
            <a href="{{ route('admin.pix-payments.index') }}" class="bg-neutral-800 hover:bg-neutral-700 text-white px-6 py-2 rounded font-bold transition-colors">
                Limpar
            </a>
        @endif
    </form>
</div>

<!-- Tabela -->
<div class="bg-neutral-900 border border-neutral-800 rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-neutral-300">
            <thead class="text-xs text-neutral-400 uppercase bg-neutral-950 border-b border-neutral-800">
                <tr>
                    <th class="px-6 py-4">ID Transação</th>
                    <th class="px-6 py-4">Usuário</th>
                    <th class="px-6 py-4">Plano</th>
                    <th class="px-6 py-4">Valor</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Criado em / Pago em</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr class="border-b border-neutral-800 hover:bg-neutral-800/50 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs">
                            <span class="text-neutral-500">ID Local:</span> #{{ $payment->id }}<br>
                            <span class="text-neutral-500">MP ID:</span> {{ $payment->mp_payment_id ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4">
                            @if($payment->user)
                                <div class="font-bold text-white">{{ $payment->user->name }}</div>
                                <div class="text-xs text-neutral-400">{{ $payment->user->email }}</div>
                            @else
                                <span class="text-neutral-500 italic">Usuário Removido</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($payment->plan)
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-semibold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                    {{ $payment->plan->name }}
                                </span>
                            @else
                                <span class="text-neutral-500 italic">Plano Removido</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold text-white">
                            R$ {{ number_format($payment->amount, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($payment->status === 'approved')
                                <span class="px-2 py-1 bg-green-500/10 text-green-500 border border-green-500/20 rounded text-xs font-bold uppercase">Aprovado</span>
                            @elseif($payment->status === 'pending')
                                <span class="px-2 py-1 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded text-xs font-bold uppercase">Pendente</span>
                            @elseif($payment->status === 'cancelled')
                                <span class="px-2 py-1 bg-neutral-500/10 text-neutral-400 border border-neutral-500/20 rounded text-xs font-bold uppercase">Expirado</span>
                            @else
                                <span class="px-2 py-1 bg-red-500/10 text-red-500 border border-red-500/20 rounded text-xs font-bold uppercase">{{ $payment->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div class="text-neutral-400"><i class="fa-regular fa-calendar-plus mr-1"></i> {{ $payment->created_at->format('d/m/Y H:i') }}</div>
                            @if($payment->paid_at)
                                <div class="text-green-400 mt-1"><i class="fa-regular fa-calendar-check mr-1"></i> {{ $payment->paid_at->format('d/m/Y H:i') }}</div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-neutral-500">
                            Nenhum pagamento encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($payments->hasPages())
        <div class="p-4 border-t border-neutral-800">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection

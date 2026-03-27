@extends('layouts.admin')

@section('title', 'Cupons e Assinaturas')

@section('content')
<section>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Cupons de Acesso</h2>
            <p class="text-sm text-neutral-500">Gere códigos para ativação de planos Basic e Premium.</p>
        </div>

        <a href="{{ route('admin.coupons.create') }}" class="bg-netflix hover:bg-red-700 text-white font-bold px-6 py-2.5 rounded shadow-lg transition flex items-center gap-2">
            <i class="fa-solid fa-plus"></i> GERAR NOVO CUPOM
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-900/20 border border-green-600 text-green-400 px-4 py-3 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-neutral-900 border border-neutral-800 rounded-xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-neutral-800/50 text-neutral-400">
                    <tr>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">CÓDIGO</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">PLANO DESTINO</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">DURAÇÃO</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">USOS</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">BENEFÍCIOS</th>
                        <th class="p-4 font-bold uppercase tracking-widest text-[10px]">STATUS</th>
                        <th class="p-4 text-right font-bold uppercase tracking-widest text-[10px]">AÇÕES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-neutral-800/20 transition group">
                            <td class="p-4">
                                <div class="bg-neutral-800 border border-neutral-700 font-mono text-netflix font-bold px-3 py-1 rounded inline-block">
                                    {{ $coupon->code }}
                                </div>
                            </td>
                            <td class="p-4">
                                @if($coupon->plan === 'premium')
                                    <span class="flex items-center gap-1.5 text-yellow-500 font-bold uppercase text-[10px]">
                                        <i class="fa-solid fa-crown"></i> PREMIUM
                                    </span>
                                @else
                                    <span class="flex items-center gap-1.5 text-blue-400 font-bold uppercase text-[10px]">
                                        <i class="fa-solid fa-star"></i> BASIC
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-neutral-300 font-medium font-mono">
                                {{ $coupon->days }} DIAS
                            </td>
                            <td class="p-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex justify-between text-[10px] text-neutral-500 mb-1">
                                        <span>Utilizado</span>
                                        <span>{{ $coupon->used_count }} / {{ $coupon->max_uses ?? '∞' }}</span>
                                    </div>
                                    <div class="w-full bg-neutral-800 rounded-full h-1">
                                        @php
                                            $percent = $coupon->max_uses ? ($coupon->used_count / $coupon->max_uses) * 100 : 0;
                                        @endphp
                                        <div class="bg-netflix h-1 rounded-full" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <div class="flex flex-wrap gap-1">
                                    @if($coupon->features && count($coupon->features) > 0)
                                        @foreach($coupon->features as $f)
                                            <span class="bg-neutral-800 text-neutral-500 text-[9px] px-2 py-0.5 rounded border border-neutral-700/50 uppercase">{{ $f }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-[10px] text-neutral-600">Padrão do Plano</span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-4">
                                @if($coupon->is_active)
                                    <span class="bg-green-900/20 text-green-500 text-[10px] font-bold px-2.5 py-1 rounded-full border border-green-900/30">ATIVO</span>
                                @else
                                    <span class="bg-red-900/20 text-red-500 text-[10px] font-bold px-2.5 py-1 rounded-full border border-red-900/30">PAUSADO</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-3 opacity-20 group-hover:opacity-100 transition">
                                    <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-neutral-400 hover:text-white transition">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="inline" onsubmit="return confirm('Deletar cupom permanentemente?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-400 transition">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-10 text-center text-neutral-500">
                                <i class="fa-solid fa-ticket text-3xl mb-3 block opacity-20"></i>
                                Nenhum cupom gerado até o momento.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">
        {{ $coupons->links() }}
    </div>
</section>
@endsection

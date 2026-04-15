@extends('layouts.admin')

@section('title', 'Editar Plano')

@section('content')
<section class="max-w-4xl">
    <div class="mb-8">
        <a href="{{ route('admin.subscription-plans.index') }}" class="text-netflix hover:text-red-600 text-sm mb-4 inline-block">
            <i class="fa-solid fa-arrow-left"></i> Voltar para Planos
        </a>
        <h2 class="text-2xl font-bold text-white">Editar Plano: {{ $subscriptionPlan->name }}</h2>
        <p class="text-neutral-500 text-sm">Atualize os detalhes de preço, nível e benefícios deste plano.</p>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-900/20 border border-red-600 text-red-400 px-4 py-3 rounded text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $erro)
                    <li>{{ $erro }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.subscription-plans.update', $subscriptionPlan) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Nome do Plano</label>
                    <input type="text" name="name" value="{{ old('name', $subscriptionPlan->name) }}" required
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Categoria do Plano</label>
                    <input type="text" name="plan_category" value="{{ old('plan_category', $subscriptionPlan->plan_category) }}" placeholder="Ex: Mensal, Anual, Premium Pack"
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                    <p class="text-[10px] text-neutral-500 mt-1">Agrupa planos iguais em carrosséis horizontais no App.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Nível de Conta</label>
                    <select name="plan_type" required class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                        <option value="premium" {{ old('plan_type', $subscriptionPlan->plan_type) == 'premium' ? 'selected' : '' }}>Premium (Múltiplas Telas, 4K, Tudo liberado)</option>
                        <option value="basic" {{ old('plan_type', $subscriptionPlan->plan_type) == 'basic' ? 'selected' : '' }}>Basic (720p, Telas limitadas)</option>
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Preço com Desconto (R$)</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $subscriptionPlan->price) }}" required
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Preço Original (R$)</label>
                    <input type="number" step="0.01" name="original_price" value="{{ old('original_price', $subscriptionPlan->original_price) }}"
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                    <p class="text-[10px] text-neutral-500 mt-1">Opcional. Ativa o selo de % OFF no App.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Duração (Dias)</label>
                    <input type="number" name="duration_days" value="{{ old('duration_days', $subscriptionPlan->duration_days) }}" required
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-neutral-500 uppercase mb-4">Benefícios e Selos na Conta</label>
                <div class="grid md:grid-cols-2 gap-4">
                    @php $features = $subscriptionPlan->features ?? []; @endphp
                    
                    <label class="flex items-center gap-3 cursor-pointer group bg-neutral-800 p-3 rounded">
                        <input type="checkbox" name="feature_no_ads" value="1" {{ old('feature_no_ads', in_array('no_ads', $features)) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded">
                        <span class="text-sm text-neutral-300 group-hover:text-white transition">Assistir Sem Anúncios</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer group bg-neutral-800 p-3 rounded">
                        <input type="checkbox" name="feature_priority_support" value="1" {{ old('feature_priority_support', in_array('priority_support', $features)) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded">
                        <span class="text-sm text-neutral-300 group-hover:text-white transition">Suporte Prioritário</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer group bg-neutral-800 p-3 rounded">
                        <input type="checkbox" name="feature_priority_requests" value="1" {{ old('feature_priority_requests', in_array('priority_requests', $features)) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded">
                        <span class="text-sm text-neutral-300 group-hover:text-white transition">Fazer Pedidos de Filmes (TMDB)</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer group bg-neutral-800 p-3 rounded">
                        <input type="checkbox" name="feature_premium_channels" value="1" {{ old('feature_premium_channels', in_array('premium_channels', $features)) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded">
                        <span class="text-sm text-neutral-300 group-hover:text-white transition">Canais de TV Fechada VIP</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Custo em Pontos (Check-in Diário)</label>
                <input type="number" name="points_cost" value="{{ old('points_cost', $subscriptionPlan->points_cost) }}" min="0" placeholder="Ex: 30"
                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                <p class="text-[10px] text-neutral-500 mt-1">Opcional. Se preenchido, usuários poderão trocar pontos por este plano.</p>
            </div>

            <div>
                <label class="flex items-center gap-3 cursor-pointer group pt-4 border-t border-neutral-800 mt-4">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $subscriptionPlan->is_active) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded">
                    <span class="text-sm text-neutral-300 group-hover:text-white transition">Plano Ativo (Disponível para os usuários)</span>
                </label>
            </div>

        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" class="bg-netflix hover:bg-red-700 text-white font-bold px-8 py-3 rounded transition">
                Salvar Atualizações
            </button>
        </div>
    </form>
</section>
@endsection

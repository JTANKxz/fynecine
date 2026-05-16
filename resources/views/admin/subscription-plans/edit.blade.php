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

            <div class="grid md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Preço (R$)</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $subscriptionPlan->price) }}" required
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Preço Original (R$)</label>
                    <input type="number" step="0.01" name="original_price" value="{{ old('original_price', $subscriptionPlan->original_price) }}"
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                    <p class="text-[10px] text-neutral-500 mt-1">Opcional. Ativa o selo OFF no App.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Desconto 1ª Assinatura (R$)</label>
                    <input type="number" step="0.01" name="first_time_discount" value="{{ old('first_time_discount', $subscriptionPlan->first_time_discount) }}"
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                    <p class="text-[10px] text-neutral-500 mt-1">Opcional. Aplicado para novos assinantes.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Duração (Dias)</label>
                    <input type="number" name="duration_days" value="{{ old('duration_days', $subscriptionPlan->duration_days) }}" required
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                </div>
            </div>
            
            <div>
                <div class="flex justify-between items-center mb-4">
                    <label class="block text-xs font-bold text-neutral-500 uppercase">Benefícios do Plano</label>
                    <button type="button" id="add-feature-btn" class="bg-neutral-800 hover:bg-neutral-700 text-white text-xs px-3 py-1.5 rounded transition">
                        + Adicionar Benefício
                    </button>
                </div>
                
                <div id="features-container" class="space-y-3">
                    <!-- Features will be added here via JS -->
                </div>
                
                <p class="text-[10px] text-neutral-500 mt-2">Estes benefícios serão mostrados na tela de planos do app. Marque o checkbox para mostrar o ícone de ✔️ ou deixe desmarcado para mostrar ❌.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Custo em Pontos (Check-in Diário)</label>
                <input type="number" name="points_cost" value="{{ old('points_cost', $subscriptionPlan->points_cost) }}" min="0" placeholder="Ex: 30"
                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                <p class="text-[10px] text-neutral-500 mt-1">Opcional. Se preenchido, usuários poderão trocar pontos por este plano.</p>
            </div>

            <!-- PROMOÇÃO / OFERTA -->
            <div class="bg-neutral-800/30 p-6 rounded-xl border border-neutral-700/50 space-y-6">
                <h3 class="text-white font-bold text-sm flex items-center gap-2">
                    <i class="fa-solid fa-tag text-netflix"></i> Promoção / Oferta de Tempo Limitado
                </h3>
                
                <div class="grid md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Preço Promocional (R$)</label>
                        <input type="number" step="0.01" name="offer_price" value="{{ old('offer_price', $subscriptionPlan->offer_price) }}"
                               class="w-full bg-neutral-900 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                        <p class="text-[10px] text-neutral-500 mt-1">Se preenchido, este preço substituirá o preço normal durante o período.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Válido Até</label>
                        <input type="datetime-local" name="offer_expires_at" value="{{ old('offer_expires_at', $subscriptionPlan->offer_expires_at ? $subscriptionPlan->offer_expires_at->format('Y-m-d\TH:i') : '') }}"
                               class="w-full bg-neutral-900 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                        <p class="text-[10px] text-neutral-500 mt-1">Data e hora que a promoção encerra (Timer no App).</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Label de Desconto (Badge)</label>
                        <input type="text" name="discount_label" value="{{ old('discount_label', $subscriptionPlan->discount_label) }}" placeholder="Ex: 50% OFF"
                               class="w-full bg-neutral-900 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                        <p class="text-[10px] text-neutral-500 mt-1">Texto exibido na etiqueta de desconto.</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-4 pt-4 border-t border-neutral-800 mt-4">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="is_popular" value="1" {{ old('is_popular', $subscriptionPlan->is_popular) ? 'checked' : '' }} class="w-5 h-5 accent-yellow-500 rounded">
                    <div class="flex flex-col">
                        <span class="text-sm text-neutral-300 group-hover:text-white transition">Marcar como "Popular"</span>
                        <span class="text-[10px] text-neutral-500">Exibe o selo de destaque no plano no aplicativo.</span>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $subscriptionPlan->is_active) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded">
                    <div class="flex flex-col">
                        <span class="text-sm text-neutral-300 group-hover:text-white transition">Plano Ativo</span>
                        <span class="text-[10px] text-neutral-500">Disponível para os usuários visualizarem e assinarem.</span>
                    </div>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('features-container');
        const addBtn = document.getElementById('add-feature-btn');
        let featureIndex = 0;
        
        // Features atuais do banco (json)
        const existingFeatures = @json($subscriptionPlan->features ?? []);

        function addFeatureRow(name = '', included = true) {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 bg-neutral-800 p-2 rounded';
            
            const isChecked = included ? 'checked' : '';
            
            row.innerHTML = `
                <div class="flex-1">
                    <input type="text" name="features[${featureIndex}][name]" value="${name}" placeholder="Ex: Sem anúncios" required
                           class="w-full bg-neutral-900 border border-neutral-700 text-white rounded px-3 py-2 text-sm focus:ring-1 focus:ring-netflix outline-none">
                </div>
                <label class="flex items-center gap-2 cursor-pointer px-2">
                    <input type="checkbox" name="features[${featureIndex}][included]" value="1" ${isChecked} class="w-4 h-4 accent-netflix rounded">
                    <span class="text-xs text-neutral-400">Incluído</span>
                </label>
                <button type="button" class="text-neutral-500 hover:text-red-500 p-2 remove-feature-btn">
                    <i class="fa-solid fa-trash"></i>
                </button>
            `;
            
            container.appendChild(row);
            
            row.querySelector('.remove-feature-btn').addEventListener('click', function() {
                row.remove();
            });
            
            featureIndex++;
        }

        addBtn.addEventListener('click', () => addFeatureRow());
        
        // Popular se houver features salvas, caso contrário colocar um em branco
        if (existingFeatures && existingFeatures.length > 0) {
            existingFeatures.forEach(feature => {
                // Suporte legado se os features antigos (no_ads, etc) ainda estiverem no banco
                if (typeof feature === 'string') {
                    let translated = feature;
                    if (feature === 'no_ads') translated = 'Assistir Sem Anúncios';
                    else if (feature === 'priority_support') translated = 'Suporte Prioritário';
                    else if (feature === 'priority_requests') translated = 'Fazer Pedidos';
                    else if (feature === 'premium_channels') translated = 'Canais VIP';
                    addFeatureRow(translated, true);
                } else if (typeof feature === 'object' && feature.name) {
                    addFeatureRow(feature.name, feature.included);
                }
            });
        } else {
            addFeatureRow('Catálogo Completo', true);
            addFeatureRow('Sem Anúncios', true);
        }
    });
</script>
@endsection

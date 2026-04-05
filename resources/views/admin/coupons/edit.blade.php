@extends('layouts.admin')

@section('title', 'Editar Cupom')

@section('content')
<section>
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold">Editar Cupom: <span class="text-netflix">{{ $coupon->code }}</span></h2>
        <a href="{{ route('admin.coupons.index') }}" class="text-sm text-neutral-400 hover:text-white transition">
            <i class="fa-solid fa-arrow-left mr-1"></i> Voltar para Lista
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-900/20 border border-red-900 text-red-500 p-4 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="bg-neutral-900 p-6 rounded-xl border border-neutral-800 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid md:grid-cols-2 gap-6">
            {{-- Código --}}
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Código Promocional</label>
                <input type="text" name="code" 
                    class="w-full p-3 bg-neutral-800 border border-neutral-700 rounded-lg focus:ring-2 focus:ring-netflix focus:border-netflix outline-none transition" 
                    value="{{ old('code', $coupon->code) }}" required>
            </div>

            {{-- Limite de Usos --}}
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Limite de Usos (Máx. Ativações)</label>
                <input type="number" name="max_uses" 
                    class="w-full p-3 bg-neutral-800 border border-neutral-700 rounded-lg focus:ring-2 focus:ring-netflix focus:border-netflix outline-none transition" 
                    value="{{ old('max_uses', $coupon->max_uses) }}" min="1">
            </div>
        </div>

        {{-- Vincular a um Plano VIP --}}
        <div class="p-4 bg-netflix/5 border border-netflix/20 rounded-lg">
            <label class="block text-sm font-bold text-netflix mb-2">Vincular a um Plano VIP Predefinido</label>
            <select name="subscription_plan_id" class="w-full p-3 bg-neutral-800 border border-neutral-700 rounded-lg focus:ring-2 focus:ring-netflix outline-none transition">
                <option value="">Nenhum Plano Associado (Usar Configuração Customizada Abaixo)</option>
                @foreach($plans ?? [] as $pln)
                    <option value="{{ $pln->id }}" {{ old('subscription_plan_id', $coupon->subscription_plan_id) == $pln->id ? 'selected' : '' }}>
                        {{ $pln->name }} — R$ {{ number_format($pln->price, 2, ',', '.') }} ({{ $pln->duration_days }} dias)
                    </option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-neutral-400 italic text-center">
                <i class="fa-solid fa-circle-info mr-1"></i> Se selecionar um plano, as configurações de "Nível" e "Duração" abaixo serão ignoradas.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-6 pt-4 border-t border-neutral-800">
            {{-- Plano Custom --}}
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Nível (Básico ou Premium)</label>
                <select name="plan" class="w-full p-3 bg-neutral-800 border border-neutral-700 rounded-lg focus:ring-2 focus:ring-netflix outline-none transition">
                    <option value="basic" {{ old('plan', $coupon->plan) == 'basic' ? 'selected' : '' }}>Basic (Até 2 Perfis)</option>
                    <option value="premium" {{ old('plan', $coupon->plan) == 'premium' ? 'selected' : '' }}>Premium (Até 5 Perfis)</option>
                </select>
            </div>

            {{-- Dias Custom --}}
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Dias de Duração</label>
                <input type="number" name="days" 
                    class="w-full p-3 bg-neutral-800 border border-neutral-700 rounded-lg focus:ring-2 focus:ring-netflix outline-none transition" 
                    value="{{ old('days', $coupon->days) }}" min="1">
            </div>
        </div>

        @php
            $features = is_array($coupon->features) ? $coupon->features : (json_decode($coupon->features, true) ?? []);
        @endphp

        {{-- Benefícios Extras --}}
        <div class="space-y-3 pt-4 border-t border-neutral-800">
            <label class="block text-sm font-bold text-neutral-300">Benefícios Extras Ativados</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <label class="flex items-center p-3 bg-neutral-800/50 border border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-800 transition">
                    <input type="checkbox" name="feature_no_ads" value="1" {{ in_array('no_ads', $features) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded border-neutral-600 bg-neutral-700">
                    <div class="ml-3">
                        <span class="block text-sm font-bold">Sem Anúncios</span>
                        <span class="block text-xs text-neutral-500">Remove banners e interstitials.</span>
                    </div>
                </label>

                <label class="flex items-center p-3 bg-neutral-800/50 border border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-800 transition">
                    <input type="checkbox" name="feature_live_events" value="1" {{ in_array('live_events', $features) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded border-neutral-600 bg-neutral-700">
                    <div class="ml-3">
                        <span class="block text-sm font-bold">Eventos Ao Vivo (Jogos)</span>
                        <span class="block text-xs text-neutral-500">Libera acesso aos jogos e transmissões ao vivo.</span>
                    </div>
                </label>
                
                <label class="flex items-center p-3 bg-neutral-800/50 border border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-800 transition">
                    <input type="checkbox" name="feature_priority_support" value="1" {{ in_array('priority_support', $features) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded border-neutral-600 bg-neutral-700">
                    <div class="ml-3">
                        <span class="block text-sm font-bold">Suporte Prioritário</span>
                        <span class="block text-xs text-neutral-500">Aumenta o limite diário de tickets de suporte.</span>
                    </div>
                </label>

                <label class="flex items-center p-3 bg-neutral-800/50 border border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-800 transition">
                    <input type="checkbox" name="feature_priority_requests" value="1" {{ in_array('priority_requests', $features) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded border-neutral-600 bg-neutral-700">
                    <div class="ml-3">
                        <span class="block text-sm font-bold">Pedidos Prioritários</span>
                        <span class="block text-xs text-neutral-500">Aumenta o limite diário de pedidos de conteúdo.</span>
                    </div>
                </label>

                <label class="flex items-center p-3 bg-neutral-800/50 border border-neutral-700 rounded-lg cursor-pointer hover:bg-neutral-800 transition">
                    <input type="checkbox" name="feature_premium_channels" value="1" {{ in_array('premium_channels', $features) ? 'checked' : '' }} class="w-5 h-5 accent-netflix rounded border-neutral-600 bg-neutral-700">
                    <div class="ml-3">
                        <span class="block text-sm font-bold">Canais de TV (IPTV)</span>
                        <span class="block text-xs text-neutral-500">Libera a grade completa de canais de TV.</span>
                    </div>
                </label>
            </div>
        </div>

        {{-- Status --}}
        <div class="flex items-center p-4 bg-neutral-800 rounded-lg">
            <div class="flex items-center h-5">
                <input id="is_active" name="is_active" type="checkbox" value="1" {{ $coupon->is_active ? 'checked' : '' }}
                    class="w-5 h-5 accent-netflix rounded border-neutral-600 bg-neutral-700">
            </div>
            <div class="ml-3 text-sm">
                <label for="is_active" class="font-medium text-white">Cupom Ativo</label>
                <p class="text-neutral-400">Permitir que usuários resgatem este código imediatamente.</p>
            </div>
        </div>

        {{-- Ações --}}
        <div class="flex flex-col md:flex-row gap-4 pt-4">
            <button type="submit" class="flex-1 bg-netflix text-white font-bold py-3 px-6 rounded-lg hover:bg-red-700 transform active:scale-95 transition flex items-center justify-center gap-2 shadow-lg shadow-netflix/20">
                <i class="fa-solid fa-save"></i> Atualizar Cupom
            </button>
            <a href="{{ route('admin.coupons.index') }}" class="md:w-32 bg-neutral-700 text-white font-medium py-3 px-6 rounded-lg hover:bg-neutral-600 text-center transition">
                Cancelar
            </a>
        </div>
    </form>
</section>
@endsection

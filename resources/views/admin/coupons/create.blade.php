@extends('layouts.admin')

@section('title', 'Gerar Cupom')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Novo Cupom</h6>
            </div>
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.coupons.store') }}" method="POST">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Código Promocional</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code', strtoupper(Str::random(8))) }}" required>
                            <small class="text-muted">Ex: VIP30, LANCAMENTO</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="font-weight-bold text-primary">Vincular a um Plano VIP (Opcional)</label>
                            <select name="subscription_plan_id" class="form-control">
                                <option value="">Nenhum Plano Associado (Cupom Customizado)</option>
                                @foreach($plans ?? [] as $pln)
                                    <option value="{{ $pln->id }}" {{ old('subscription_plan_id') == $pln->id ? 'selected' : '' }}>
                                        {{ $pln->name }} - R$ {{ number_format($pln->price, 2, ',', '.') }} ({{ $pln->duration_days }} dias)
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Se selecionado, o Nível e a Duração abaixo serão ignorados e o usuário receberá os benefícios deste plano.</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                        <div class="col-md-6">
                            <label>Plano (Se não selecionar um acima)</label>
                            <select name="plan" class="form-control">
                                <option value="basic">Basic (2 Perfis)</option>
                                <option value="premium">Premium (5 Perfis)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Dias de Duração</label>
                            <input type="number" name="days" class="form-control" value="30" min="1">
                            <small class="text-muted">Quantos dias de acesso o cupom concede.</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Limite de Usos (Max Ativações Globais)</label>
                            <input type="number" name="max_uses" class="form-control" value="" min="1">
                            <small class="text-muted">Deixe vazio para uso ilimitado (1 por pessoa).</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="d-block"><strong>Benefícios Extras Ativados Pelo Cupom</strong></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="feature_no_ads" id="f1" value="1" checked>
                            <label class="form-check-label" for="f1">Sem Anúncios</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="feature_priority_support" id="f2" value="1">
                            <label class="form-check-label" for="f2">Suporte Prioritário</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="feature_priority_requests" id="f3" value="1">
                            <label class="form-check-label" for="f3">Pedidos Prioritários</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="feature_premium_channels" id="f4" value="1">
                            <label class="form-check-label" for="f4">Canais Premium (TV ao Vivo)</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" checked>
                            <label class="form-check-label" for="isActive">Cupom Ativo (Permite Resgate Agora)</label>
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">Salvar Cupom</button>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Voltar</a>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

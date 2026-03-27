@extends('layouts.admin')

@section('title', 'Editar Cupom')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Editar Cupom {{ $coupon->code }}</h6>
            </div>
            <div class="card-body">

                @if($errors->any())
                    <div class="alert alert-danger"><ul>@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul></div>
                @endif

                <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Código Promocional</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code', $coupon->code) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label>Plano</label>
                            <select name="plan" class="form-control" required>
                                <option value="basic" {{ $coupon->plan == 'basic' ? 'selected' : '' }}>Basic (2 Perfis)</option>
                                <option value="premium" {{ $coupon->plan == 'premium' ? 'selected' : '' }}>Premium (5 Perfis)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Dias de Duração</label>
                            <input type="number" name="days" class="form-control" value="{{ old('days', $coupon->days) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label>Limite de Usos Globais</label>
                            <input type="number" name="max_uses" class="form-control" value="{{ old('max_uses', $coupon->max_uses) }}">
                        </div>
                    </div>

                    @php
                        $features = is_array($coupon->features) ? $coupon->features : [];
                    @endphp

                    <div class="mb-4">
                        <label class="d-block"><strong>Benefícios Extras</strong></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="feature_no_ads" id="f1" value="1" {{ in_array('no_ads', $features) ? 'checked' : '' }}>
                            <label for="f1">Sem Anúncios</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="feature_priority_support" id="f2" value="1" {{ in_array('priority_support', $features) ? 'checked' : '' }}>
                            <label for="f2">Suporte Prioritário</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="feature_priority_requests" id="f3" value="1" {{ in_array('priority_requests', $features) ? 'checked' : '' }}>
                            <label for="f3">Pedidos Prioritários</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ $coupon->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActive">Cupom Ativo</label>
                        </div>
                    </div>

                    <button class="btn btn-primary" type="submit">Atualizar</button>
                    <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Voltar</a>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

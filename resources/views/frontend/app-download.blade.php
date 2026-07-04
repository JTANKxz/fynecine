@extends('layouts.fyne')

@section('title', 'Baixar Aplicativo - ' . ($settings->app_name ?? 'FYNECINE'))
@section('nav_app_active', 'active')

@section('seo')
    <meta name="description" content="Baixe o aplicativo {{ $settings->app_name ?? 'FYNECINE' }} e tenha acesso a milhares de filmes e séries grátis!">
@endsection

@section('styles')
<style>
    .download-page {
        min-height: calc(100vh - 140px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
        background: radial-gradient(circle at top right, rgba(124, 58, 237, 0.15) 0%, transparent 40%),
                    radial-gradient(circle at bottom left, rgba(168, 85, 247, 0.1) 0%, transparent 40%);
        animation: fadeIn 0.4s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .download-card {
        background: rgba(10, 10, 10, 0.8);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(124, 58, 237, 0.3);
        border-radius: 24px;
        padding: 40px;
        text-align: center;
        max-width: 500px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.8), 0 0 40px rgba(124, 58, 237, 0.1);
    }
    .download-icon {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        border-radius: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        box-shadow: 0 10px 30px rgba(124, 58, 237, 0.4);
    }
    .download-icon i {
        font-size: 48px;
        color: #fff;
    }
    .download-title {
        font-size: 32px;
        font-weight: 800;
        color: #f0f2f5;
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }
    .download-desc {
        color: #b0b8c4;
        font-size: 16px;
        line-height: 1.6;
        margin-bottom: 32px;
    }
    .btn-download {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: #7c3aed;
        color: #fff;
        text-decoration: none;
        padding: 16px 32px;
        border-radius: 40px;
        font-size: 18px;
        font-weight: 700;
        transition: 0.3s;
        width: 100%;
        border: none;
        cursor: pointer;
    }
    .btn-download:hover {
        background: #a855f7;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
        color: #fff;
    }
    .btn-download i {
        font-size: 24px;
    }
    .btn-disabled {
        background: #2a2a2a;
        color: #6b7385;
        cursor: not-allowed;
    }
    .btn-disabled:hover {
        background: #2a2a2a;
        transform: none;
        box-shadow: none;
        color: #6b7385;
    }
    .features {
        display: flex;
        justify-content: center;
        gap: 24px;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
    }
    .feature-item {
        color: #8a94a6;
        font-size: 13px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .feature-item i {
        font-size: 20px;
        color: #a855f7;
    }
</style>
@endsection

@section('content')
<div class="download-page">
    <div class="download-card">
        <div class="download-icon">
            <i class="fab fa-android"></i>
        </div>
        <h1 class="download-title">Aplicativo Oficial</h1>
        <p class="download-desc">
            Leve o <strong>{{ $settings->app_name ?? 'FYNECINE' }}</strong> no seu bolso! Baixe nosso aplicativo para Android e assista a milhares de filmes e séries grátis, sem travamentos.
        </p>

        @if(!empty($settings->update_url))
            <a href="{{ $settings->update_url }}" class="btn-download" target="_blank" rel="noopener">
                <i class="fas fa-download"></i> Baixar para Android
            </a>
        @else
            <button class="btn-download btn-disabled" disabled>
                <i class="fas fa-times-circle"></i> Download Indisponível
            </button>
            <p style="color: #ef4444; font-size: 13px; margin-top: 12px;">O link de download será disponibilizado em breve.</p>
        @endif

        <div class="features">
            <div class="feature-item">
                <i class="fas fa-bolt"></i>
                <span>Super Rápido</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-tv"></i>
                <span>Alta Qualidade</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-mobile-alt"></i>
                <span>Para Celular</span>
            </div>
        </div>
    </div>
</div>
@endsection

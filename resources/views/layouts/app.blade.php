<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    @php
        $settings = \App\Models\AppConfig::getSettings();
        $title = $title ?? $settings->app_name . ' - Assistir Filmes e Séries Online Grátis';
        $description = $description ?? 'A melhor plataforma para assistir filmes e séries online grátis dublado e legendado em HD. O melhor do cinema na sua casa sem pagar nada!';
        $image = $image ?? asset('img/banner-default.jpg'); // Placeholder or default
        $url = url()->current();
    @endphp

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="assistir filmes online, filmes gratis, series online gratis, assistir avatar online, fynecine, filmes dublados, series legendadas">
    <meta name="robots" content="index, follow">
    
    <!-- OpenGraph (Facebook, WhatsApp, Instagram, Telegram) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $image }}">
    <meta property="og:site_name" content="{{ $settings->app_name }}">
    <meta property="og:locale" content="pt_BR">

    <!-- Twitter / X -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $url }}">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ $image }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/frontend.css') }}">

    @yield('styles')
</head>
<body>

    @include('components.navbar')

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="logo">
                        <i class="fas fa-play-circle"></i> {{ $settings->app_name }}
                    </div>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 1rem;">
                        Assistir filmes online grátis, séries online grátis e muito mais. Plataforma 100% gratuita para os amantes do cinema.
                    </p>
                </div>
                <div class="footer-links">
                    <h4>Navegação</h4>
                    <ul>
                        <li><a href="{{ route('home') }}">Início</a></li>
                        <li><a href="{{ route('frontend.search') }}">Buscar</a></li>
                        <li><a href="/terms">Termos de Uso</a></li>
                        <li><a href="/privacy">Política de Privacidade</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Redes Sociais</h4>
                    <div style="display: flex; gap: 1rem; font-size: 1.5rem;">
                        @if($settings->is_telegram_active)
                        <a href="{{ $settings->telegram_url }}" target="_blank"><i class="fab fa-telegram"></i></a>
                        @endif
                        @if($settings->is_instagram_active)
                        <a href="{{ $settings->instagram_url }}" target="_blank"><i class="fab fa-instagram"></i></a>
                        @endif
                        @if($settings->is_whatsapp_active)
                        <a href="{{ $settings->whatsapp_url }}" target="_blank"><i class="fab fa-whatsapp"></i></a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $settings->app_name }}. Todos os direitos reservados. Assistir filmes grátis e séries online.</p>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(0, 0, 0, 0.98)';
                navbar.style.height = '60px';
            } else {
                navbar.style.background = 'var(--bg-amoled)';
                navbar.style.height = '70px';
            }
        });

        // Drawer Logic
        function toggleMenu() {
            const menu = document.getElementById('menu-drawer');
            const search = document.getElementById('search-drawer');
            const overlay = document.querySelector('.drawer-overlay');

            search.classList.remove('active');
            menu.classList.toggle('active');
            
            if (menu.classList.contains('active')) {
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            } else {
                overlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }

        function toggleSearch() {
            const menu = document.getElementById('menu-drawer');
            const search = document.getElementById('search-drawer');
            const overlay = document.querySelector('.drawer-overlay');

            menu.classList.remove('active');
            search.classList.toggle('active');

            if (search.classList.contains('active')) {
                overlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                setTimeout(() => document.getElementById('search-input').focus(), 300);
            } else {
                overlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        }

        function closeAllDrawers() {
            document.getElementById('menu-drawer').classList.remove('active');
            document.getElementById('search-drawer').classList.remove('active');
            document.querySelector('.drawer-overlay').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    </script>
    
    @yield('scripts')
</body>
</html>

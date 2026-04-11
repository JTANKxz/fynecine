<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    @php
        $settings = \App\Models\AppConfig::getSettings();
        $title = $title ?? "Assistir Filmes Online Grátis - {$settings->app_name}";
        $description = $description ?? 'A melhor plataforma para assistir filmes online, assistir filmes online egrátis, assistir series online gratis e dublado em HD. O melhor do cinema na sua casa sem pagar nada!';
        $keywords = "assistir filmes online, assistir filmes online egratis, assistir series online gratis, assistir filmes online dublado, filmes HD gratis, fynecine, series dubladas";
        $url = url()->current();
    @endphp

    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="keywords" content="{{ $keywords }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $url }}">

    <!-- OpenGraph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $url }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:site_name" content="{{ $settings->app_name }}">
    <meta property="og:locale" content="pt_BR">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --bg-body: #080808;
            --bg-nav: rgba(10, 10, 10, 0.95);
            --bg-card: #121212;
            --text-primary: #ffffff;
            --text-secondary: #a0a0a0;
            --accent: #ffffff;
            --primary: #8b5cf6;
            --container-max: 1400px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            line-height: 1.6;
        }

        h1, h2, h3, .logo {
            font-family: 'Outfit', sans-serif;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
        }

        .container {
            max-width: var(--container-max);
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Navbar */
        header {
            height: 70px;
            background: var(--bg-nav);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 900;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i { color: var(--primary); }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
        }

        .nav-links a:hover { color: var(--primary); }

        /* Main Content */
        main {
            margin-top: 70px;
        }

        /* Footer */
        footer {
            background: #050505;
            padding: 4rem 0 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.03);
            margin-top: 4rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 768px) {
            .footer-grid { grid-template-columns: 1fr; gap: 2rem; }
        }

        .footer-about p {
            color: var(--text-secondary);
            font-size: 0.95rem;
            margin-top: 1rem;
            max-width: 400px;
        }

        .footer-title {
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .footer-links a:hover { color: var(--text-primary); }

        .footer-copyright {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.03);
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        /* Section Styling */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
        }

        /* Scroll Styling */
        .scroll-container {
            display: flex;
            gap: 1.2rem;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 1rem;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE 10+ */
        }

        .scroll-container::-webkit-scrollbar {
            display: none; /* Safari and Chrome */
        }

        /* Card Styling */
        .movie-card {
            flex: 0 0 clamp(140px, 18vw, 200px);
            position: relative;
            transition: var(--transition);
        }

        .movie-card:hover {
            transform: translateY(-5px);
        }

        .card-img-wrapper {
            width: 100%;
            aspect-ratio: 2/3;
            border-radius: 12px;
            overflow: hidden;
            background: #1a1a1a;
            position: relative;
            box-shadow: 0 10px 20px rgba(0,0,0,0.5);
        }

        .card-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .movie-card:hover .card-img {
            transform: scale(1.05);
        }

        .card-info {
            margin-top: 0.8rem;
        }

        .card-title {
            font-size: 0.95rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .card-meta {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-top: 0.2rem;
        }

        /* Genre Scroll */
        .genre-chips {
            display: flex;
            gap: 0.8rem;
            overflow-x: auto;
            padding: 1rem 0;
            scrollbar-width: none;
        }

        .genre-chip {
            white-space: nowrap;
            background: rgba(255, 255, 255, 0.05);
            padding: 0.6rem 1.4rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .genre-chip:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        /* Hero Slider */
        .hero {
            position: relative;
            height: 75vh;
            min-height: 500px;
            overflow: hidden;
        }

        .hero-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transition: opacity 1s ease;
            display: flex;
            align-items: center;
        }

        .hero-slide.active { opacity: 1; }

        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: -1;
        }

        .hero-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to right, rgba(8, 8, 8, 0.9) 20%, transparent 80%),
                        linear-gradient(to top, rgba(8, 8, 8, 1) 0%, transparent 40%);
        }

        .hero-content {
            position: relative;
            z-index: 10;
            max-width: 650px;
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            line-height: 1.1;
            margin-bottom: 1.5rem;
            font-weight: 900;
        }

        .btn-play {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            background: white;
            color: black;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .btn-play:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.05);
        }
    </style>
    @yield('styles')
</head>
<body>

    <header>
        <div class="container navbar">
            <a href="/" class="logo">
                <i class="fas fa-play-circle"></i> {{ $settings->app_name }}
            </a>
            <ul class="nav-links">
                <li><a href="/">Home</a></li>
                <li><a href="{{ route('frontend.search') }}">Buscar</a></li>
                @if($settings->is_telegram_active)
                    <li><a href="{{ $settings->telegram_url }}" target="_blank">Telegram</a></li>
                @endif
            </ul>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-about">
                    <div class="logo">
                        <i class="fas fa-play-circle"></i> {{ $settings->app_name }}
                    </div>
                    <p>Assistir filmes online, assistir filmes online egrátis, assistir series online gratis. A melhor plataforma para os amantes do cinema!</p>
                </div>
                <div>
                    <h4 class="footer-title">Links Úteis</h4>
                    <ul class="footer-links">
                        <li><a href="/terms">Termos de Uso</a></li>
                        <li><a href="/privacy">Privacidade</a></li>
                        <li><a href="{{ route('frontend.search') }}">Busca Avançada</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title">Categorias</h4>
                    <ul class="footer-links">
                        <li><a href="/">Filmes Dublados</a></li>
                        <li><a href="/">Séries Online</a></li>
                        <li><a href="/">Lançamentos HD</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-copyright">
                &copy; {{ date('Y') }} {{ $settings->app_name }}. Todos os direitos reservados.
            </div>
        </div>
    </footer>

    <script>
        // Hero Slider logic if needed
        let currentSlide = 0;
        const slides = document.querySelectorAll('.hero-slide');
        
        function nextSlide() {
            if(!slides.length) return;
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }

        if(slides.length > 1) {
            setInterval(nextSlide, 7000);
        }
    </script>
    @yield('scripts')
</body>
</html>

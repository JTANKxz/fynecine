<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>@yield('title', \App\Models\AppConfig::getSettings()->app_name ?? 'FYNECINE' . ' - Filmes e Séries')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <style>
        /* ----- RESET & BASE - TEMA AMOLED + ROXO SEM NEON ----- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #000000;
            color: #f0f2f5;
            padding-bottom: 80px;
            overflow-x: hidden;
            min-height: 100vh;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-thumb {
            background: #6b21a5;
            border-radius: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0a0a0a;
        }

        /* ----- HEADER ----- */
        .header {
            background: linear-gradient(180deg, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.8) 100%);
            padding: 16px 20px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header.fixed {
            position: fixed;
            left: 0;
            right: 0;
        }
        .logo {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #a855f7;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .logo i {
            color: #a855f7;
            font-size: 22px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
        }
        .header-actions i {
            font-size: 20px;
            color: #b0b8c4;
            cursor: pointer;
            transition: 0.2s;
        }
        .header-actions i:hover {
            color: #a855f7;
        }
        .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #7c3aed;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            color: #fff;
            cursor: pointer;
            border: 2px solid #4b1d8a;
        }

        /* ----- SEARCH BAR SEM NEON ----- */
        .search-container {
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }
        .search-container form {
            display: flex;
            align-items: center;
        }
        .search-input {
            width: 0;
            padding: 0;
            border: none;
            border-radius: 30px;
            background: #1a1a1a;
            color: #fff;
            font-size: 14px;
            outline: none;
            transition: width 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                padding 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                opacity 0.3s ease,
                border 0.3s ease;
            opacity: 0;
            pointer-events: none;
            border: 1.5px solid transparent;
            height: 36px;
        }
        .search-input.open {
            width: 180px;
            padding: 0 16px;
            opacity: 1;
            pointer-events: auto;
            border-color: #7c3aed;
        }
        .search-input:focus {
            border-color: #a855f7;
        }
        .search-toggle {
            font-size: 20px;
            color: #b0b8c4;
            cursor: pointer;
            transition: 0.2s;
            background: none;
            border: none;
            padding: 4px;
            margin-left: 8px;
        }
        .search-toggle:hover {
            color: #a855f7;
        }
        .search-toggle.active {
            color: #a855f7;
        }

        @media (max-width: 480px) {
            .search-input.open {
                width: 130px;
                padding: 0 12px;
                font-size: 13px;
            }
        }

        /* ----- MENU INFERIOR SEM NEON ----- */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #000000;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid #1a1a1a;
            display: flex;
            justify-content: space-around;
            padding: 8px 0 12px;
            z-index: 100;
            box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.8);
        }
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            font-size: 10px;
            color: #6b7385;
            cursor: pointer;
            transition: 0.2s;
            border: none;
            background: none;
            font-family: inherit;
            padding: 0 8px;
        }
        .nav-item i {
            font-size: 22px;
            transition: 0.2s;
        }
        .nav-item.active {
            color: #a855f7;
        }
        .nav-item.active i {
            color: #a855f7;
            transform: translateY(-2px);
        }
        .nav-item:hover {
            color: #c084fc;
        }

        /* ----- CARD FLUTUANTE SEM NEON ----- */
        .floating-card {
            position: fixed;
            z-index: 9999;
            width: 340px;
            max-width: 90vw;
            background: #0a0a0a;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.9);
            border: 1px solid #7c3aed;
            overflow: hidden;
            pointer-events: none;
            visibility: hidden;
            opacity: 0;
            transform: scale(0.85) translateY(10px);
            transition: opacity 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                transform 0.2s cubic-bezier(0.25, 0.46, 0.45, 0.94),
                visibility 0.2s;
        }
        .floating-card.active {
            visibility: visible;
            opacity: 1;
            transform: scale(1) translateY(0);
            pointer-events: auto;
        }
        .floating-card .banner {
            width: 100%;
            height: 170px;
            background-size: cover;
            background-position: center;
            flex-shrink: 0;
        }
        .floating-card .info {
            padding: 12px 16px 14px;
            background: #0a0a0a;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .floating-card .info .titulo {
            font-size: 18px;
            font-weight: 700;
            color: #f0f2f5;
        }
        .floating-card .info .meta {
            display: flex;
            align-items: center;
            gap: 14px;
            font-size: 14px;
            color: #b0b8c4;
        }
        .floating-card .info .meta .avaliacao {
            display: flex;
            align-items: center;
            gap: 4px;
            color: #fbbf24;
        }
        .floating-card .info .meta .avaliacao i {
            font-size: 13px;
        }
        .floating-card .info .sinopse {
            font-size: 13px;
            color: #b0b8c4;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
            margin: 2px 0 6px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .floating-card .info .botoes {
            display: flex;
            gap: 12px;
            margin-top: auto;
            padding-top: 4px;
        }
        .floating-card .info .botoes button {
            flex: 1;
            padding: 10px 0;
            border: none;
            border-radius: 30px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .btn-assistir {
            background: #7c3aed;
            color: #fff;
        }
        .btn-assistir:hover {
            background: #a855f7;
        }
        .btn-salvar {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            border: 1px solid rgba(124, 58, 237, 0.2) !important;
        }
        .btn-salvar:hover {
            background: rgba(124, 58, 237, 0.25);
            border-color: #7c3aed !important;
        }

        @media (max-width: 480px) {
            .floating-card {
                width: 280px;
            }
            .floating-card .banner {
                height: 130px;
            }
            .floating-card .info .titulo {
                font-size: 16px;
            }
            .floating-card .info .meta {
                font-size: 12px;
                gap: 10px;
            }
            .floating-card .info .sinopse {
                font-size: 12px;
            }
            .floating-card .info .botoes button {
                font-size: 12px;
                padding: 8px 0;
            }
        }
        
        /* Loading Spinner */
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
            width: 100%;
        }
        .loading-spinner i {
            font-size: 24px;
            color: #7c3aed;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }

    </style>
    @yield('styles')
</head>
<body class="@yield('body_class')">

    <!-- HEADER -->
    <header class="header @yield('header_class')">
        <a href="{{ route('home') }}" class="logo" id="logoHome">
            <i class="fas fa-film"></i> {{ \App\Models\AppConfig::getSettings()->app_name ?? 'FYNECINE' }}
        </a>
        <div class="header-actions">
            <!-- Search com animação suave -->
            <div class="search-container">
                <form action="{{ route('frontend.search') }}" method="GET" style="display: flex;">
                    <input type="text" name="q" value="{{ request('q') }}" class="search-input" id="searchInput" placeholder="Buscar filmes, séries..." />
                    <button type="button" class="search-toggle" id="searchToggle" aria-label="Buscar">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <i class="fas fa-bell"></i>
            <div class="avatar" onclick="window.location.href='{{ route('login') }}'">
                {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 1)) : 'F' }}
            </div>
        </div>
    </header>

    @yield('content')

    <!-- MENU INFERIOR -->
    <nav class="bottom-nav">
        <button class="nav-item @yield('nav_home_active')" data-tab="home" onclick="window.location.href='{{ route('home') }}'"><i class="fas fa-home"></i><span>Início</span></button>
        <button class="nav-item @yield('nav_catalogo_active')" data-tab="catalogo" onclick="window.location.href='{{ route('frontend.search') }}'"><i class="fas fa-compass"></i><span>Explorar</span></button>
        <button class="nav-item" data-tab="lista" onclick="window.location.href='{{ auth()->check() ? route('login') : route('login') }}'"><i class="fas fa-list"></i><span>Minha Lista</span></button>
        <button class="nav-item" data-tab="perfil" onclick="window.location.href='{{ route('login') }}'"><i class="fas fa-user"></i><span>Perfil</span></button>
    </nav>

    <!-- CARD FLUTUANTE -->
    <div class="floating-card" id="floatingCard">
        <div class="banner" id="floatBanner"></div>
        <div class="info">
            <div class="titulo" id="floatTitulo"></div>
            <div class="meta">
                <span class="avaliacao" id="floatAvaliacao"><i class="fas fa-star"></i> 0.0</span>
                <span id="floatAno"></span>
                <span id="floatDuracao"></span>
            </div>
            <div class="sinopse" id="floatSinopse"></div>
            <div class="botoes">
                <button class="btn-assistir" id="floatAssistir"><i class="fas fa-play"></i> Assistir agora</button>
                <button class="btn-salvar" id="floatSalvar" onclick="alert('Funcionalidade em desenvolvimento!')"><i class="fas fa-plus"></i> Salvar</button>
            </div>
        </div>
    </div>

    <script>
        // ----- SEARCHBAR COM ANIMAÇÃO SUAVE -----
        const searchInput = document.getElementById('searchInput');
        const searchToggle = document.getElementById('searchToggle');
        const searchForm = searchInput.closest('form');

        searchToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = searchInput.classList.contains('open');
            if (isOpen && searchInput.value.trim() !== '') {
                searchForm.submit();
            } else {
                searchInput.classList.toggle('open');
                searchToggle.classList.toggle('active');
                if (!isOpen) {
                    searchInput.focus();
                } else {
                    searchInput.blur();
                }
            }
        });

        // Fechar search ao clicar fora
        document.addEventListener('click', (e) => {
            const container = document.querySelector('.search-container');
            if (!container.contains(e.target)) {
                searchInput.classList.remove('open');
                searchToggle.classList.remove('active');
                searchInput.blur();
            }
        });

        // Enter para buscar
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && searchInput.value.trim()) {
                searchForm.submit();
            }
        });

        // ----- CARD FLUTUANTE LÓGICA COMPARTILHADA -----
        const floating = document.getElementById('floatingCard');
        const floatBanner = document.getElementById('floatBanner');
        const floatTitulo = document.getElementById('floatTitulo');
        const floatSinopse = document.getElementById('floatSinopse');
        const floatAvaliacao = document.getElementById('floatAvaliacao');
        const floatAno = document.getElementById('floatAno');
        const floatDuracao = document.getElementById('floatDuracao');
        const floatAssistir = document.getElementById('floatAssistir');

        let hideTimeout = null;
        let activeCard = null;
        let pendingShow = null;

        function fillCard(card) {
            const titulo = card.dataset.titulo;
            const ano = card.dataset.ano;
            const nota = parseFloat(card.dataset.nota) || 0;
            const duracao = parseInt(card.dataset.duracao) || 0;
            const img = card.dataset.img;
            const sinopse = card.dataset.sinopse || 'Sinopse não disponível.';
            const url = card.dataset.url;

            floatBanner.style.backgroundImage = `url('${img}')`;
            floatTitulo.textContent = titulo;
            floatSinopse.textContent = sinopse;
            floatAvaliacao.innerHTML = `<i class="fas fa-star"></i> ${nota.toFixed(1)}`;
            floatAno.textContent = ano;
            
            if (duracao > 0) {
                floatDuracao.textContent = `${duracao} min`;
                floatDuracao.style.display = 'inline';
            } else {
                floatDuracao.style.display = 'none';
            }

            floatAssistir.onclick = (e) => {
                e.stopPropagation();
                window.location.href = url;
            };

            const rect = card.getBoundingClientRect();
            const floatWidth = floating.offsetWidth || 340;
            const floatHeight = floating.offsetHeight || 400;

            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;

            let left = centerX - floatWidth / 2;
            let top = centerY - floatHeight / 2;

            const margin = 10;
            if (left < margin) left = margin;
            if (left + floatWidth > window.innerWidth - margin) {
                left = window.innerWidth - floatWidth - margin;
            }
            if (top < margin) top = margin;
            if (top + floatHeight > window.innerHeight - margin) {
                top = window.innerHeight - floatHeight - margin;
            }

            floating.style.left = left + 'px';
            floating.style.top = top + 'px';
        }

        function showFloatingCard(event, card) {
            if (window.innerWidth <= 600) return;

            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }
            if (pendingShow) {
                clearTimeout(pendingShow);
                pendingShow = null;
            }

            if (activeCard === card && floating.classList.contains('active')) {
                return;
            }

            if (activeCard && activeCard !== card) {
                floating.classList.remove('active');
                pendingShow = setTimeout(() => {
                    pendingShow = null;
                    fillCard(card);
                    floating.classList.add('active');
                    activeCard = card;
                }, 80);
                return;
            }

            fillCard(card);
            floating.classList.add('active');
            activeCard = card;
        }

        function hideFloatingCard() {
            if (window.innerWidth <= 600) return;
            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }
            if (pendingShow) {
                clearTimeout(pendingShow);
                pendingShow = null;
            }
            hideTimeout = setTimeout(() => {
                floating.classList.remove('active');
                activeCard = null;
                hideTimeout = null;
            }, 150);
        }

        function attachCardEvents(cards) {
            cards.forEach(card => {
                card.addEventListener('mouseenter', (e) => {
                    if (window.innerWidth > 600) {
                        showFloatingCard(e, card);
                    }
                });
                card.addEventListener('mouseleave', () => {
                    if (window.innerWidth > 600) {
                        hideFloatingCard();
                    }
                });
            });
        }

        // Attach on initial load
        attachCardEvents(document.querySelectorAll('.card'));

        floating.addEventListener('mouseenter', () => {
            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }
            if (pendingShow) {
                clearTimeout(pendingShow);
                pendingShow = null;
            }
        });
        floating.addEventListener('mouseleave', hideFloatingCard);

        window.addEventListener('scroll', () => {
            if (activeCard && floating.classList.contains('active')) {
                const rect = activeCard.getBoundingClientRect();
                const isVisible = rect.bottom > 0 && rect.top < window.innerHeight &&
                    rect.right > 0 && rect.left < window.innerWidth;
                if (!isVisible) {
                    hideFloatingCard();
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>

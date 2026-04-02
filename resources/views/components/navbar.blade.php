<nav class="navbar">
    <div class="container navbar-content">
        <a href="{{ route('home') }}" class="logo">
            <i class="fas fa-play-circle"></i> {{ \App\Models\AppConfig::getSettings()->app_name }}
        </a>

        <div class="nav-actions">
            <div class="search-trigger" onclick="toggleSearch()">
                <i class="fas fa-search"></i>
            </div>
            
            <div class="hamburger" onclick="toggleMenu()">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </div>
</nav>

<!-- Drawer Overlay -->
<div class="drawer-overlay" onclick="closeAllDrawers()"></div>

<!-- Search Drawer -->
<div id="search-drawer" class="drawer">
    <div class="container">
        <div class="search-drawer-content">
            <form action="{{ route('frontend.search') }}" method="GET" class="search-input-wrapper">
                <input type="text" name="q" placeholder="O que você quer assistir hoje?" autocomplete="off" id="search-input">
            </form>
        </div>
    </div>
</div>

<!-- Menu Drawer -->
<div id="menu-drawer" class="drawer">
    <div class="container">
        <div class="menu-drawer-content" style="max-width: 100%;">
            <ul class="menu-list">
                <li>
                    <a href="{{ route('home') }}" class="menu-item {{ request()->routeIs('home') ? 'active' : '' }}">
                        <i class="fas fa-home"></i> Início
                    </a>
                </li>
                <li>
                    <a href="{{ route('frontend.search') }}" class="menu-item">
                        <i class="fas fa-compass"></i> Explorar
                    </a>
                </li>
                <li>
                    <a href="{{ route('frontend.search', ['type' => 'movie']) }}" class="menu-item">
                        <i class="fas fa-film"></i> Filmes
                    </a>
                </li>
                <li>
                    <a href="{{ route('frontend.search', ['type' => 'series']) }}" class="menu-item">
                        <i class="fas fa-tv"></i> Séries
                    </a>
                </li>
                <li>
                    <a href="{{ route('frontend.search') }}" class="menu-item">
                        <i class="fas fa-layer-group"></i> Gêneros
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

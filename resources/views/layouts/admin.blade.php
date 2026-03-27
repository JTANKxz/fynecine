<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - @yield('title')</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        netflix: "#e50914",
                        netflixDark: "#0f0f0f"
                    }
                }
            }
        }
    </script>
    <style>
        /* Estilo para manter proporção das capas de filmes (formato retrato 2:3) */
        .movie-poster {
            aspect-ratio: 2/3;
            object-fit: cover;
            width: 60px;
            height: 90px;
        }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1a1a1a;
        }

        ::-webkit-scrollbar-thumb {
            background: #e50914;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #ff0a16;
        }

        /* Estilo para tabelas responsivas com scroll */
        .table-container {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #e50914 #1a1a1a;
        }

        table {
            min-width: 800px;
        }

        /* Para telas muito pequenas, reduzir ainda mais o min-width */
        @media (max-width: 640px) {
            table {
                min-width: 600px;
            }
        }
    </style>
</head>

<body class="bg-black text-white">

    <!-- OVERLAY -->
    <div id="overlay" class="fixed inset-0 bg-black/70 hidden z-40" onclick="toggleMenu()"></div>

    <!-- SIDEBAR -->
    <aside id="sidebar"
        class="fixed left-0 top-0 h-full w-72 bg-neutral-950 border-r border-neutral-800 transform -translate-x-full md:translate-x-0 transition duration-300 z-50 overflow-y-auto">
        <div class="flex items-center justify-between p-5 border-b border-neutral-800">
            <h2 class="text-xl font-bold text-netflix">ADMIN</h2>
            <button onclick="toggleMenu()" class="md:hidden text-2xl">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <nav class="p-4 space-y-2">

            {{-- Dashboard --}}
            <div>
                <a href="{{ route('admin.dash') }}"
                   class="block p-3 rounded flex items-center gap-2 {{ request()->routeIs('admin.dash') ? 'bg-netflix text-white' : 'hover:bg-neutral-800' }}">
                    <i class="fa-solid fa-chart-line w-5"></i> Dashboard
                </a>
            </div>

            {{-- Filmes --}}
            <div class="space-y-1">
                <button onclick="toggleSubmenu('filmesSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between {{ request()->routeIs('admin.movies.*') || request()->routeIs('admin.links.movies') ? 'text-netflix' : '' }}">
                    <span><i class="fa-solid fa-film mr-2"></i> Filmes</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-filmesSubmenu"></i>
                </button>
                <div id="filmesSubmenu" class="{{ request()->routeIs('admin.movies.*') || request()->routeIs('admin.links.movies') ? '' : 'hidden' }} pl-8 space-y-1">
                    <a href="{{ route('admin.movies.index') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.movies.index') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-list w-4 mr-1"></i> Todos os Filmes
                    </a>
                    <a href="{{ route('admin.links.movies') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.links.movies') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-link w-4 mr-1"></i> Gerenciar Links
                    </a>
                    <a href="{{ route('admin.tmdb') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.tmdb') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-cloud-arrow-down w-4 mr-1"></i> Importar via TMDB
                    </a>
                </div>
            </div>

            {{-- Séries --}}
            <div class="space-y-1">
                <button onclick="toggleSubmenu('seriesSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between {{ request()->routeIs('admin.series.*') || request()->routeIs('admin.links.series') ? 'text-netflix' : '' }}">
                    <span><i class="fa-solid fa-tv mr-2"></i> Séries</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-seriesSubmenu"></i>
                </button>
                <div id="seriesSubmenu" class="{{ request()->routeIs('admin.series.*') || request()->routeIs('admin.links.series') ? '' : 'hidden' }} pl-8 space-y-1">
                    <a href="{{ route('admin.series.index') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.series.index') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-list w-4 mr-1"></i> Todas as Séries
                    </a>
                    <a href="{{ route('admin.links.series') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.links.series') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-link w-4 mr-1"></i> Gerenciar Links
                    </a>
                    <a href="{{ route('admin.tmdb') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.tmdb') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-cloud-arrow-down w-4 mr-1"></i> Importar via TMDB
                    </a>
                </div>
            </div>

            {{-- TV ao Vivo --}}
            <div class="space-y-1">
                <button onclick="toggleSubmenu('channelsSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between {{ request()->routeIs('admin.channels.*') || request()->routeIs('admin.channel-categories.*') ? 'text-netflix' : '' }}">
                    <span><i class="fa-solid fa-satellite-dish mr-2"></i> TV ao Vivo</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-channelsSubmenu"></i>
                </button>
                <div id="channelsSubmenu" class="{{ request()->routeIs('admin.channels.*') || request()->routeIs('admin.channel-categories.*') ? '' : 'hidden' }} pl-8 space-y-1">
                    <a href="{{ route('admin.channels.index') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.channels.index') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-list w-4 mr-1"></i> Todos os Canais
                    </a>
                    <a href="{{ route('admin.channels.create') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.channels.create') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-plus w-4 mr-1"></i> Novo Canal
                    </a>
                    <a href="{{ route('admin.channel-categories.index') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.channel-categories.*') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-tags w-4 mr-1"></i> Categorias
                    </a>
                </div>
            </div>

            {{-- Sliders --}}
            <div class="space-y-1">
                <button onclick="toggleSubmenu('slidersSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between {{ request()->routeIs('admin.sliders.*') ? 'text-netflix' : '' }}">
                    <span><i class="fa-solid fa-images mr-2"></i> Sliders</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-slidersSubmenu"></i>
                </button>
                <div id="slidersSubmenu" class="{{ request()->routeIs('admin.sliders.*') ? '' : 'hidden' }} pl-8 space-y-1">
                    <a href="{{ route('admin.sliders.index') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.sliders.index') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-list w-4 mr-1"></i> Todos os Sliders
                    </a>
                    <a href="{{ route('admin.sliders.create') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.sliders.create') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-plus w-4 mr-1"></i> Adicionar Slider
                    </a>
                </div>
            </div>

            {{-- Organização da Home --}}
            <div class="space-y-1">
                <button onclick="toggleSubmenu('homeSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between {{ request()->routeIs('admin.sections.*') || request()->routeIs('admin.networks.*') ? 'text-netflix' : '' }}">
                    <span><i class="fa-solid fa-layer-group mr-2"></i> Página Inicial</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-homeSubmenu"></i>
                </button>
                <div id="homeSubmenu" class="{{ request()->routeIs('admin.sections.*') || request()->routeIs('admin.networks.*') ? '' : 'hidden' }} pl-8 space-y-1">
                    <a href="{{ route('admin.sections.index') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.sections.*') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-bars-staggered w-4 mr-1"></i> Seções da Home
                    </a>
                    <a href="{{ route('admin.networks.index') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.networks.*') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-tower-broadcast w-4 mr-1"></i> Networks
                    </a>
                </div>
            </div>

            {{-- Usuários --}}
            <div class="space-y-1">
                <button onclick="toggleSubmenu('usuariosSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.comments.*') ? 'text-netflix' : '' }}">
                    <span><i class="fa-solid fa-users mr-2"></i> Comunidade</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-usuariosSubmenu"></i>
                </button>
                <div id="usuariosSubmenu" class="{{ request()->routeIs('admin.users.*') || request()->routeIs('admin.comments.*') ? '' : 'hidden' }} pl-8 space-y-1">
                    <a href="{{ route('admin.users.index') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.users.index') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-list w-4 mr-1"></i> Todos os Usuários
                    </a>
                    <a href="{{ route('admin.comments.index') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.comments.*') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-comments w-4 mr-1"></i> Moderação (Comentários)
                    </a>
                    <a href="{{ route('admin.requests.index') }}"
                       class="block p-2 rounded text-sm {{ request()->routeIs('admin.requests.*') ? 'bg-neutral-700 text-white' : 'hover:bg-neutral-800' }}">
                        <i class="fa-solid fa-hand-holding-heart w-4 mr-1"></i> Pedidos (Requests)
                    </a>
                </div>
            </div>

            {{-- Configurações Gerais --}}
            <div>
                <a href="{{ route('admin.settings.edit') }}"
                   class="block p-3 rounded flex items-center gap-2 {{ request()->routeIs('admin.settings.edit') ? 'bg-netflix text-white' : 'hover:bg-neutral-800' }}">
                    <i class="fa-solid fa-cogs w-5"></i> Configurações
                </a>
            </div>

            {{-- Separador --}}
            <div class="border-t border-neutral-800 my-2"></div>

            {{-- Planos de Assinatura --}}
            <div>
                <a href="{{ route('admin.subscription-plans.index') }}"
                   class="block p-3 rounded flex items-center gap-2 {{ request()->routeIs('admin.subscription-plans.*') ? 'bg-netflix text-white' : 'hover:bg-neutral-800' }}">
                    <i class="fa-solid fa-crown w-5"></i> Planos VIP
                </a>
            </div>

            {{-- Cupons VIP --}}
            <div>
                <a href="{{ route('admin.coupons.index') }}"
                   class="block p-3 rounded flex items-center gap-2 {{ request()->routeIs('admin.coupons.*') ? 'bg-netflix text-white' : 'hover:bg-neutral-800' }}">
                    <i class="fa-solid fa-ticket-alt w-5"></i> Cupons VIP
                </a>
            </div>

            {{-- TMDB (atalho direto) --}}
            <div>
                <a href="{{ route('admin.tmdb') }}"
                   class="block p-3 rounded flex items-center gap-2 {{ request()->routeIs('admin.tmdb') ? 'bg-netflix text-white' : 'hover:bg-neutral-800' }}">
                    <i class="fa-solid fa-database w-5"></i> TMDB
                </a>
            </div>

        </nav>
    </aside>


    <!-- MAIN -->
    <div class="md:ml-72">
        <!-- HEADER -->
        <header class="flex items-center justify-between p-5 border-b border-neutral-800 bg-black sticky top-0 z-30">
            <button onclick="toggleMenu()" class="text-2xl md:hidden">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="font-bold text-lg">Dashboard</h1>
            <div class="relative">
                <button onclick="toggleUserMenu()" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-netflix rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-user text-sm"></i>
                    </div>
                </button>
                <!-- User Submenu -->
                <div id="userMenu"
                    class="hidden absolute right-0 mt-2 w-48 bg-neutral-900 rounded-lg shadow-lg border border-neutral-800 z-40">
                    <a href="#" class="block px-4 py-2 hover:bg-neutral-800">Perfil</a>
                    <a href="#" class="block px-4 py-2 hover:bg-neutral-800">Configurações</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block px-4 py-2 hover:bg-neutral-800 w-full text-left">
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="p-6 space-y-10">
            @yield('content')
        </main>
    </div>
    <script>
        function toggleMenu() {
            const sidebar = document.getElementById("sidebar");
            const overlay = document.getElementById("overlay");
            sidebar.classList.toggle("-translate-x-full");
            overlay.classList.toggle("hidden");
        }

        function toggleUserMenu() {
            const userMenu = document.getElementById("userMenu");
            userMenu.classList.toggle("hidden");
        }

        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const arrow = document.getElementById(`arrow-${submenuId}`);
            submenu.classList.toggle("hidden");
            arrow.classList.toggle("rotate-180");
        }

        // Carregar resultados iniciais
        window.onload = () => {

            // Fechar user menu ao clicar fora
            document.addEventListener('click', (e) => {
                const userMenu = document.getElementById('userMenu');
                const userButton = document.querySelector('.fa-user').parentElement;

                if (!userButton.contains(e.target) && !userMenu.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        };
    </script>
</body>

</html>

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
            <!-- Menu principal com submenus -->
            <div>
                <a href="{{ route('admin.dash') }}" class="block p-3 rounded hover:bg-neutral-800">
                    <i class="fa-solid fa-chart-line mr-2"></i> Dashboard
                </a>
            </div>

            <!-- Filmes com submenu -->
            <div class="space-y-1">
                <button onclick="toggleSubmenu('filmesSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between">
                    <span><i class="fa-solid fa-film mr-2"></i> Filmes</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-filmesSubmenu"></i>
                </button>
                <div id="filmesSubmenu" class="hidden pl-8 space-y-1">
                    <a href="{{ route('admin.movies.index') }}" class="block p-2 rounded hover:bg-neutral-800 text-sm">Todos os Filmes</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Adicionar Filme</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Categorias</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Em Destaque</a>
                </div>
            </div>

            <!-- Séries com submenu -->
            <div class="space-y-1">
                <button onclick="toggleSubmenu('seriesSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between">
                    <span><i class="fa-solid fa-tv mr-2"></i> Séries</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-seriesSubmenu"></i>
                </button>
                <div id="seriesSubmenu" class="hidden pl-8 space-y-1">
                    <a href="{{ route('admin.series.index') }}" class="block p-2 rounded hover:bg-neutral-800 text-sm">Todas as Séries</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Adicionar Série</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Temporadas</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Episódios</a>
                </div>
            </div>

            <!-- Usuários com submenu -->
            <div class="space-y-1">
                <button onclick="toggleSubmenu('usuariosSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between">
                    <span><i class="fa-solid fa-users mr-2"></i> Usuários</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-usuariosSubmenu"></i>
                </button>
                <div id="usuariosSubmenu" class="hidden pl-8 space-y-1">
                    <a href="{{ route('admin.users.index') }}" class="block p-2 rounded hover:bg-neutral-800 text-sm">Todos os Usuários</a>
                    <a href="{{ route('admin.users.create') }}" class="block p-2 rounded hover:bg-neutral-800 text-sm">Adicionar Usuário</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Permissões</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Bloqueados</a>
                </div>
            </div>

            <!-- Eventos com submenu -->
            <div class="space-y-1">
                <button onclick="toggleSubmenu('eventosSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between">
                    <span><i class="fa-solid fa-calendar mr-2"></i> Eventos</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-eventosSubmenu"></i>
                </button>
                <div id="eventosSubmenu" class="hidden pl-8 space-y-1">
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Todos os Eventos</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Próximos Eventos</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Passados</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Criar Evento</a>
                </div>
            </div>

            <!-- Componentes com submenu -->
            <div class="space-y-1">
                <button onclick="toggleSubmenu('componentesSubmenu')"
                    class="w-full text-left p-3 rounded hover:bg-neutral-800 flex items-center justify-between">
                    <span><i class="fa-solid fa-puzzle-piece mr-2"></i> Componentes</span>
                    <i class="fa-solid fa-chevron-down text-sm transition-transform" id="arrow-componentesSubmenu"></i>
                </button>
                <div id="componentesSubmenu" class="hidden pl-8 space-y-1">
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Botões</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Inputs</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Cards</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Modais</a>
                    <a href="#" class="block p-2 rounded hover:bg-neutral-800 text-sm">Tabelas</a>
                </div>
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

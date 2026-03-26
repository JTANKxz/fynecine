@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<!-- STATS -->
<section>
    <h2 class="text-xl font-bold mb-4">Estatísticas</h2>
    <div class="grid md:grid-cols-4 gap-4">
        <div class="bg-neutral-900 p-5 rounded hover:bg-neutral-800 transition">
            <p class="text-neutral-400">Filmes</p>
            <p class="text-3xl font-bold">120</p>
        </div>
        <div class="bg-neutral-900 p-5 rounded hover:bg-neutral-800 transition">
            <p class="text-neutral-400">Séries</p>
            <p class="text-3xl font-bold">80</p>
        </div>
        <div class="bg-neutral-900 p-5 rounded hover:bg-neutral-800 transition">
            <p class="text-neutral-400">Usuários</p>
            <p class="text-3xl font-bold">540</p>
        </div>
        <div class="bg-neutral-900 p-5 rounded hover:bg-neutral-800 transition">
            <p class="text-neutral-400">Eventos</p>
            <p class="text-3xl font-bold">12</p>
        </div>
    </div>
</section>

<!-- TABELA DE USUÁRIOS -->
<section>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Usuários Cadastrados</h2>
        <button class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
            <i class="fa-solid fa-plus mr-2"></i>Novo Usuário
        </button>
    </div>
    <div class="bg-neutral-900 rounded-lg overflow-hidden">
        <div class="table-container">
            <table class="w-full">
                <thead class="bg-neutral-800">
                    <tr>
                        <th class="text-left p-4">ID</th>
                        <th class="text-left p-4">Nome</th>
                        <th class="text-left p-4">Email</th>
                        <th class="text-left p-4">Tipo</th>
                        <th class="text-left p-4">Status</th>
                        <th class="text-left p-4">Último acesso</th>
                        <th class="text-left p-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                        <td class="p-4">#001</td>
                        <td class="p-4">João Silva</td>
                        <td class="p-4">joao@email.com</td>
                        <td class="p-4"><span
                                class="bg-blue-600/20 text-blue-500 px-2 py-1 rounded text-sm">Admin</span></td>
                        <td class="p-4"><span
                                class="bg-green-600/20 text-green-500 px-2 py-1 rounded text-sm">Ativo</span></td>
                        <td class="p-4">10/03/2024</td>
                        <td class="p-4">
                            <button class="text-blue-500 hover:text-blue-400 mr-2"><i
                                    class="fa-solid fa-edit"></i></button>
                            <button class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                        <td class="p-4">#002</td>
                        <td class="p-4">Maria Santos</td>
                        <td class="p-4">maria@email.com</td>
                        <td class="p-4"><span
                                class="bg-purple-600/20 text-purple-500 px-2 py-1 rounded text-sm">Editor</span></td>
                        <td class="p-4"><span
                                class="bg-green-600/20 text-green-500 px-2 py-1 rounded text-sm">Ativo</span></td>
                        <td class="p-4">09/03/2024</td>
                        <td class="p-4">
                            <button class="text-blue-500 hover:text-blue-400 mr-2"><i
                                    class="fa-solid fa-edit"></i></button>
                            <button class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                        <td class="p-4">#003</td>
                        <td class="p-4">Pedro Oliveira</td>
                        <td class="p-4">pedro@email.com</td>
                        <td class="p-4"><span
                                class="bg-gray-600/20 text-gray-500 px-2 py-1 rounded text-sm">Usuário</span></td>
                        <td class="p-4"><span
                                class="bg-yellow-600/20 text-yellow-500 px-2 py-1 rounded text-sm">Pendente</span></td>
                        <td class="p-4">08/03/2024</td>
                        <td class="p-4">
                            <button class="text-blue-500 hover:text-blue-400 mr-2"><i
                                    class="fa-solid fa-edit"></i></button>
                            <button class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                        <td class="p-4">#004</td>
                        <td class="p-4">Ana Costa</td>
                        <td class="p-4">ana@email.com</td>
                        <td class="p-4"><span
                                class="bg-gray-600/20 text-gray-500 px-2 py-1 rounded text-sm">Usuário</span></td>
                        <td class="p-4"><span
                                class="bg-red-600/20 text-red-500 px-2 py-1 rounded text-sm">Bloqueado</span></td>
                        <td class="p-4">07/03/2024</td>
                        <td class="p-4">
                            <button class="text-blue-500 hover:text-blue-400 mr-2"><i
                                    class="fa-solid fa-edit"></i></button>
                            <button class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- TABELA DE FILMES COM IMAGENS -->
<section>
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Catálogo de Filmes</h2>
        <button class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition text-sm">
            <i class="fa-solid fa-plus mr-2"></i>Adicionar Filme
        </button>
    </div>
    <div class="bg-neutral-900 rounded-lg overflow-hidden">
        <div class="table-container">
            <table class="w-full">
                <thead class="bg-neutral-800">
                    <tr>
                        <th class="text-left p-4">Capa</th>
                        <th class="text-left p-4">ID</th>
                        <th class="text-left p-4">Título</th>
                        <th class="text-left p-4">Ano</th>
                        <th class="text-left p-4">Gênero</th>
                        <th class="text-left p-4">Duração</th>
                        <th class="text-left p-4">Classificação</th>
                        <th class="text-left p-4">Avaliação</th>
                        <th class="text-left p-4">Status</th>
                        <th class="text-left p-4">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                        <td class="p-2">
                            <img src="https://image.tmdb.org/t/p/w500/8UlWHLMpgZm9bx6QYh0NFoq67TZ.jpg"
                                class="movie-poster rounded"
                                onerror="this.src='https://via.placeholder.com/60x90?text=Sem+Imagem'">
                        </td>
                        <td class="p-4">#101</td>
                        <td class="p-4 font-medium">Vingadores: Ultimato</td>
                        <td class="p-4">2019</td>
                        <td class="p-4">Ação</td>
                        <td class="p-4">181 min</td>
                        <td class="p-4"><span
                                class="bg-red-600/20 text-red-500 px-2 py-1 rounded text-sm">12</span></td>
                        <td class="p-4">
                            <div class="flex items-center">
                                <i class="fa-solid fa-star text-yellow-500 mr-1"></i>
                                8.4
                            </div>
                        </td>
                        <td class="p-4"><span
                                class="bg-green-600/20 text-green-500 px-2 py-1 rounded text-sm">Disponível</span></td>
                        <td class="p-4">
                            <button class="text-blue-500 hover:text-blue-400 mr-2"><i
                                    class="fa-solid fa-edit"></i></button>
                            <button class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                        <td class="p-2">
                            <img src="https://image.tmdb.org/t/p/w500/fVqKDO4aQdON0kHm55LwZ9B2I7E.jpg"
                                class="movie-poster rounded"
                                onerror="this.src='https://via.placeholder.com/60x90?text=Sem+Imagem'">
                        </td>
                        <td class="p-4">#102</td>
                        <td class="p-4 font-medium">Oppenheimer</td>
                        <td class="p-4">2023</td>
                        <td class="p-4">Drama</td>
                        <td class="p-4">180 min</td>
                        <td class="p-4"><span
                                class="bg-red-600/20 text-red-500 px-2 py-1 rounded text-sm">16</span></td>
                        <td class="p-4">
                            <div class="flex items-center">
                                <i class="fa-solid fa-star text-yellow-500 mr-1"></i>
                                8.6
                            </div>
                        </td>
                        <td class="p-4"><span
                                class="bg-green-600/20 text-green-500 px-2 py-1 rounded text-sm">Disponível</span></td>
                        <td class="p-4">
                            <button class="text-blue-500 hover:text-blue-400 mr-2"><i
                                    class="fa-solid fa-edit"></i></button>
                            <button class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                        <td class="p-2">
                            <img src="https://image.tmdb.org/t/p/w500/rLmFc7JpFLRlMvA6d8zA4J5QqZk.jpg"
                                class="movie-poster rounded"
                                onerror="this.src='https://via.placeholder.com/60x90?text=Sem+Imagem'">
                        </td>
                        <td class="p-4">#103</td>
                        <td class="p-4 font-medium">Duna: Parte 2</td>
                        <td class="p-4">2024</td>
                        <td class="p-4">Ficção</td>
                        <td class="p-4">166 min</td>
                        <td class="p-4"><span
                                class="bg-red-600/20 text-red-500 px-2 py-1 rounded text-sm">14</span></td>
                        <td class="p-4">
                            <div class="flex items-center">
                                <i class="fa-solid fa-star text-yellow-500 mr-1"></i>
                                8.9
                            </div>
                        </td>
                        <td class="p-4"><span
                                class="bg-green-600/20 text-green-500 px-2 py-1 rounded text-sm">Disponível</span></td>
                        <td class="p-4">
                            <button class="text-blue-500 hover:text-blue-400 mr-2"><i
                                    class="fa-solid fa-edit"></i></button>
                            <button class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    <tr class="border-b border-neutral-800 hover:bg-neutral-800/50">
                        <td class="p-2">
                            <img src="https://image.tmdb.org/t/p/w500/9xjZS2rlVxm8SFx8kPC3aIGCOYQ.jpg"
                                class="movie-poster rounded"
                                onerror="this.src='https://via.placeholder.com/60x90?text=Sem+Imagem'">
                        </td>
                        <td class="p-4">#104</td>
                        <td class="p-4 font-medium">Pobres Criaturas</td>
                        <td class="p-4">2023</td>
                        <td class="p-4">Comédia</td>
                        <td class="p-4">141 min</td>
                        <td class="p-4"><span
                                class="bg-red-600/20 text-red-500 px-2 py-1 rounded text-sm">18</span></td>
                        <td class="p-4">
                            <div class="flex items-center">
                                <i class="fa-solid fa-star text-yellow-500 mr-1"></i>
                                8.1
                            </div>
                        </td>
                        <td class="p-4"><span class="bg-yellow-600/20 text-yellow-500 px-2 py-1 rounded text-sm">Em
                                breve</span></td>
                        <td class="p-4">
                            <button class="text-blue-500 hover:text-blue-400 mr-2"><i
                                    class="fa-solid fa-edit"></i></button>
                            <button class="text-red-500 hover:text-red-400"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- SEÇÃO DE COMPONENTES REUTILIZÁVEIS -->
<section>
    <h2 class="text-xl font-bold mb-4">Componentes Reutilizáveis</h2>

    <!-- Botões -->
    <div class="bg-neutral-900 p-5 rounded mb-4">
        <h3 class="font-bold mb-3 text-netflix">Botões</h3>
        <div class="flex flex-wrap gap-3">
            <button class="bg-netflix px-4 py-2 rounded hover:bg-red-700 transition"><i
                    class="fa-solid fa-save mr-2"></i>Salvar</button>
            <button class="bg-green-600 px-4 py-2 rounded hover:bg-green-700 transition"><i
                    class="fa-solid fa-check mr-2"></i>Confirmar</button>
            <button class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700 transition"><i
                    class="fa-solid fa-edit mr-2"></i>Editar</button>
            <button class="bg-yellow-600 px-4 py-2 rounded hover:bg-yellow-700 transition"><i
                    class="fa-solid fa-exclamation-triangle mr-2"></i>Atenção</button>
            <button class="bg-neutral-700 px-4 py-2 rounded hover:bg-neutral-600 transition"><i
                    class="fa-solid fa-times mr-2"></i>Cancelar</button>
            <button
                class="border border-netflix px-4 py-2 rounded text-netflix hover:bg-netflix hover:text-white transition"><i
                    class="fa-solid fa-download mr-2"></i>Importar</button>
            <button class="bg-transparent px-4 py-2 rounded hover:bg-neutral-800 transition"><i
                    class="fa-solid fa-trash mr-2"></i>Remover</button>
            <button disabled class="bg-neutral-600 px-4 py-2 rounded opacity-50 cursor-not-allowed"><i
                    class="fa-solid fa-spinner fa-spin mr-2"></i>Carregando</button>
        </div>
    </div>

    <!-- Inputs -->
    <div class="bg-neutral-900 p-5 rounded mb-4">
        <h3 class="font-bold mb-3 text-netflix">Inputs</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Texto</label>
                <input class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="Digite algo...">
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Email</label>
                <input type="email"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="email@exemplo.com">
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Senha</label>
                <input type="password"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="********">
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Número</label>
                <input type="number"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="0">
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Data</label>
                <input type="date"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Arquivo</label>
                <input type="file"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm text-neutral-400 mb-1">Textarea</label>
            <textarea class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none" rows="3"
                placeholder="Descrição detalhada..."></textarea>
        </div>
        <div class="mt-4 flex gap-6">
            <label class="flex items-center gap-2">
                <input type="checkbox" class="w-4 h-4 accent-netflix">
                <span>Ativo</span>
            </label>
            <label class="flex items-center gap-2">
                <input type="radio" name="radio" class="w-4 h-4 accent-netflix">
                <span>Opção 1</span>
            </label>
            <label class="flex items-center gap-2">
                <input type="radio" name="radio" class="w-4 h-4 accent-netflix">
                <span>Opção 2</span>
            </label>
        </div>
    </div>

    <!-- Cards -->
    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-neutral-900 p-5 rounded hover:scale-105 transition">
            <i class="fa-solid fa-film text-3xl text-netflix mb-3"></i>
            <h4 class="font-bold">Card Simples</h4>
            <p class="text-sm text-neutral-400">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <button class="mt-3 bg-netflix px-3 py-1 rounded text-sm">Ação</button>
        </div>
        <div class="bg-gradient-to-br from-netflix to-red-800 p-5 rounded hover:scale-105 transition">
            <i class="fa-solid fa-star text-3xl mb-3"></i>
            <h4 class="font-bold">Card Destacado</h4>
            <p class="text-sm">Card com gradiente para destaque especial.</p>
            <button class="mt-3 bg-white text-black px-3 py-1 rounded text-sm">Ver mais</button>
        </div>
        <div class="bg-neutral-900 p-5 rounded border border-netflix hover:scale-105 transition">
            <i class="fa-solid fa-tv text-3xl text-netflix mb-3"></i>
            <h4 class="font-bold">Card com Borda</h4>
            <p class="text-sm text-neutral-400">Card com borda vermelha para ênfase.</p>
            <button
                class="mt-3 border border-netflix text-netflix px-3 py-1 rounded text-sm hover:bg-netflix hover:text-white transition">Detalhes</button>
        </div>
    </div>
</section>

<!-- FORMULÁRIO DE USUÁRIO COMPLETO -->


<!-- FORMULÁRIO DE CADASTRO DE FILME -->
<section>
    <h2 class="text-xl font-bold mb-4">Cadastro de Filme</h2>
    <form class="bg-neutral-900 p-5 rounded space-y-4">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Título</label>
                <input class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="Título do filme">
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Título original</label>
                <input class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="Original title">
            </div>
        </div>

        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Ano</label>
                <input type="number"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="2024">
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Duração (min)</label>
                <input type="number"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="120">
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Classificação</label>
                <select class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                    <option>Livre</option>
                    <option>10 anos</option>
                    <option>12 anos</option>
                    <option>14 anos</option>
                    <option>16 anos</option>
                    <option>18 anos</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Idioma</label>
                <select class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                    <option>Português</option>
                    <option>Inglês</option>
                    <option>Espanhol</option>
                    <option>Francês</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm text-neutral-400 mb-1">Gêneros</label>
            <div class="flex flex-wrap gap-3">
                <label class="flex items-center gap-1"><input type="checkbox" class="accent-netflix"> Ação</label>
                <label class="flex items-center gap-1"><input type="checkbox" class="accent-netflix">
                    Aventura</label>
                <label class="flex items-center gap-1"><input type="checkbox" class="accent-netflix"> Comédia</label>
                <label class="flex items-center gap-1"><input type="checkbox" class="accent-netflix"> Drama</label>
                <label class="flex items-center gap-1"><input type="checkbox" class="accent-netflix"> Terror</label>
                <label class="flex items-center gap-1"><input type="checkbox" class="accent-netflix"> Ficção</label>
                <label class="flex items-center gap-1"><input type="checkbox" class="accent-netflix"> Romance</label>
            </div>
        </div>

        <div>
            <label class="block text-sm text-neutral-400 mb-1">Sinopse</label>
            <textarea class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none" rows="4"
                placeholder="Descrição do filme..."></textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Capa do filme</label>
                <input type="file" accept="image/*"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <p class="text-xs text-neutral-500 mt-1">Formato recomendado: 2:3 (vertical)</p>
            </div>
            <div>
                <label class="block text-sm text-neutral-400 mb-1">Trailer (URL)</label>
                <input class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="https://youtube.com/...">
            </div>
        </div>

        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" class="w-4 h-4 accent-netflix">
                <span>Em destaque</span>
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" class="w-4 h-4 accent-netflix">
                <span>Disponível</span>
            </label>
        </div>

        <div class="flex gap-3">
            <button class="bg-netflix px-6 py-2 rounded hover:bg-red-700 transition">Cadastrar filme</button>
            <button class="bg-neutral-700 px-6 py-2 rounded hover:bg-neutral-600 transition">Limpar</button>
        </div>
    </form>
</section>

<!-- BUSCA TMDB COM FILTROS E IDIOMA PT-BR -->
<section>
    <h2 class="text-xl font-bold mb-4">Buscar no TMDB (pt-BR)</h2>
    <div class="bg-neutral-900 p-5 rounded space-y-4">
        <!-- Filtros Avançados -->
        <div class="grid md:grid-cols-5 gap-3">
            <input id="search" class="p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                placeholder="Nome do filme/série">

            <div class="relative">
                <input id="yearFrom"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="Ano inicial" type="number" min="1900" max="2025">
            </div>

            <div class="relative">
                <input id="yearTo"
                    class="w-full p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none"
                    placeholder="Ano final" type="number" min="1900" max="2025">
            </div>

            <select id="genre" class="p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <option value="">Todos os gêneros</option>
                <option value="28">Ação</option>
                <option value="12">Aventura</option>
                <option value="16">Animação</option>
                <option value="35">Comédia</option>
                <option value="80">Crime</option>
                <option value="99">Documentário</option>
                <option value="18">Drama</option>
                <option value="10751">Família</option>
                <option value="14">Fantasia</option>
                <option value="36">História</option>
                <option value="27">Terror</option>
                <option value="10402">Música</option>
                <option value="9648">Mistério</option>
                <option value="10749">Romance</option>
                <option value="878">Ficção científica</option>
                <option value="10770">Cinema TV</option>
                <option value="53">Thriller</option>
                <option value="10752">Guerra</option>
                <option value="37">Faroeste</option>
            </select>

            <select id="sortBy" class="p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <option value="popularity.desc">Mais populares</option>
                <option value="popularity.asc">Menos populares</option>
                <option value="vote_average.desc">Melhor avaliados</option>
                <option value="vote_average.asc">Pior avaliados</option>
                <option value="release_date.desc">Mais recentes</option>
                <option value="release_date.asc">Mais antigos</option>
            </select>
        </div>

        <div class="grid md:grid-cols-3 gap-3">
            <select id="type" class="p-2 bg-neutral-800 rounded focus:ring-2 focus:ring-netflix outline-none">
                <option value="movie">Filmes</option>
                <option value="tv">Séries</option>
            </select>

            <div class="flex items-center gap-2">
                <label class="flex items-center gap-2">
                    <input type="checkbox" id="adult" class="rounded accent-netflix">
                    <span>Incluir conteúdo adulto</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button onclick="searchTMDB()" class="bg-netflix rounded p-2 flex-1 hover:bg-red-700 transition">
                    <i class="fa-solid fa-search mr-2"></i>Buscar
                </button>
                <button onclick="clearFilters()"
                    class="bg-neutral-700 rounded p-2 px-4 hover:bg-neutral-600 transition">
                    <i class="fa-solid fa-eraser"></i>
                </button>
            </div>
        </div>

        <!-- Resultados com capas no formato correto -->
        <div id="results" class="grid grid-cols-2 md:grid-cols-6 gap-4 mt-5"></div>
        <div id="loading" class="hidden text-center py-10">
            <i class="fa-solid fa-spinner fa-spin text-4xl text-netflix"></i>
        </div>
    </div>
</section>

<script>
    const api = "edcd52275afd8b8c152c82f1ce3933a2";
    const language = "pt-BR"; // Idioma configurado para português do Brasil
    let currentPage = 1;
    let totalPages = 1;

    function clearFilters() {
        document.getElementById("search").value = "";
        document.getElementById("yearFrom").value = "";
        document.getElementById("yearTo").value = "";
        document.getElementById("genre").value = "";
        document.getElementById("sortBy").value = "popularity.desc";
        document.getElementById("type").value = "movie";
        document.getElementById("adult").checked = false;
        searchTMDB();
    }

    async function searchTMDB(page = 1) {
        const query = document.getElementById("search").value;
        const type = document.getElementById("type").value;
        const yearFrom = document.getElementById("yearFrom").value;
        const yearTo = document.getElementById("yearTo").value;
        const genre = document.getElementById("genre").value;
        const sortBy = document.getElementById("sortBy").value;
        const adult = document.getElementById("adult").checked;

        const loading = document.getElementById("loading");
        const container = document.getElementById("results");

        loading.classList.remove("hidden");
        container.innerHTML = "";

        try {
            let url;

            if (query) {
                // Busca com texto
                url =
                    `https://api.themoviedb.org/3/search/${type}?api_key=${api}&query=${encodeURIComponent(query)}&page=${page}&include_adult=${adult}&language=${language}`;

                if (yearFrom) url += `&year=${yearFrom}`;

                const res = await fetch(url);
                const data = await res.json();
                displayResults(data, container, type);
            } else {
                // Busca apenas com filtros (descobrir)
                url =
                    `https://api.themoviedb.org/3/discover/${type}?api_key=${api}&page=${page}&sort_by=${sortBy}&include_adult=${adult}&language=${language}`;

                if (yearFrom) {
                    if (type === 'movie') {
                        url += `&primary_release_date.gte=${yearFrom}-01-01`;
                    } else {
                        url += `&first_air_date.gte=${yearFrom}-01-01`;
                    }
                }
                if (yearTo) {
                    if (type === 'movie') {
                        url += `&primary_release_date.lte=${yearTo}-12-31`;
                    } else {
                        url += `&first_air_date.lte=${yearTo}-12-31`;
                    }
                }
                if (genre) url += `&with_genres=${genre}`;

                const res = await fetch(url);
                const data = await res.json();
                displayResults(data, container, type);
            }
        } catch (error) {
            container.innerHTML =
                `<div class="col-span-full text-center text-red-500">Erro ao buscar resultados: ${error.message}</div>`;
        } finally {
            loading.classList.add("hidden");
        }
    }

    function displayResults(data, container, type) {
        if (!data.results || data.results.length === 0) {
            container.innerHTML =
                '<div class="col-span-full text-center text-neutral-400">Nenhum resultado encontrado</div>';
            return;
        }

        container.innerHTML = "";

        data.results.slice(0, 12).forEach(item => {
            const title = item.title || item.name;
            const date = item.release_date || item.first_air_date;
            const year = date ? date.split('-')[0] : 'N/A';
            const rating = item.vote_average ? item.vote_average.toFixed(1) : 'N/A';
            const popularity = item.popularity ? Math.round(item.popularity) : 'N/A';

            container.innerHTML += `
                    <div class="bg-neutral-900 rounded overflow-hidden hover:scale-105 transition-transform cursor-pointer group">
                        <div class="relative">
                            <img src="https://image.tmdb.org/t/p/w500${item.poster_path || ''}" 
                                 class="movie-poster"
                                 onerror="this.src='https://via.placeholder.com/500x750?text=Sem+Imagem'">
                            <div class="absolute inset-0 bg-black/70 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                <button onclick="importItem(${item.id}, '${type}')" class="bg-netflix p-2 rounded-full hover:bg-red-700"><i class="fa-solid fa-download"></i></button>
                                <button onclick="showDetails(${item.id}, '${type}')" class="bg-neutral-800 p-2 rounded-full hover:bg-neutral-700"><i class="fa-solid fa-info-circle"></i></button>
                            </div>
                        </div>
                        <div class="p-3 space-y-1">
                            <p class="text-sm font-bold truncate">${title}</p>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-neutral-400">${year}</span>
                                <span class="text-yellow-500"><i class="fa-solid fa-star mr-1"></i>${rating}</span>
                            </div>
                            <div class="text-xs text-neutral-400">
                                <i class="fa-solid fa-fire mr-1 text-netflix"></i>${popularity}
                            </div>
                        </div>
                    </div>
                `;
        });

        // Adicionar paginação se necessário
        if (data.total_pages > 1) {
            container.innerHTML += `
                    <div class="col-span-full flex justify-center gap-2 mt-4">
                        <button onclick="searchTMDB(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''} 
                                class="bg-neutral-800 px-4 py-2 rounded hover:bg-neutral-700 transition ${currentPage <= 1 ? 'opacity-50 cursor-not-allowed' : ''}">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <span class="bg-neutral-800 px-4 py-2 rounded">${currentPage}</span>
                        <button onclick="searchTMDB(${currentPage + 1})" ${currentPage >= data.total_pages ? 'disabled' : ''}
                                class="bg-neutral-800 px-4 py-2 rounded hover:bg-neutral-700 transition ${currentPage >= data.total_pages ? 'opacity-50 cursor-not-allowed' : ''}">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                `;
        }
    }

    function importItem(id, type) {
        alert(`Importando ${type === 'movie' ? 'filme' : 'série'} ID: ${id} com dados em português`);
        // Aqui você implementaria a lógica de importação
    }

    function showDetails(id, type) {
        alert(`Mostrando detalhes do ${type === 'movie' ? 'filme' : 'série'} ID: ${id}`);
        // Aqui você implementaria a lógica de mostrar detalhes
    }
</script>

@endsection
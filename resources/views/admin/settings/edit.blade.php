@extends('layouts.admin')

@section('title', 'Configurações Globais')

@section('content')
<section class="max-w-6xl pb-20">
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-white">Configurações do Sistema</h2>
        <p class="text-sm text-neutral-500">Ajuste chaves de API, modos de segurança e comportamento do app.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-900/20 border border-green-600 text-green-400 px-4 py-3 rounded text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- MODO SEGURANÇA -->
        <div class="bg-red-900/10 border border-red-900/30 p-6 rounded-xl">
            <div class="flex items-start gap-4">
                <div class="bg-red-600 p-3 rounded-lg">
                    <i class="fa-solid fa-shield-halved text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold text-lg mb-1 text-red-500">Modo de Segurança (Aprovação Loja)</h3>
                    <p class="text-neutral-400 text-sm mb-4 leading-relaxed">
                        Ao ativar este modo, a API omitirá <strong>TODOS</strong> os links de reprodução. 
                        A plataforma passará a se comportar como um catálogo oficial legal (estilo "Guia de Filmes"). 
                        Ideal para usar durante a análise da Google Play e App Store.
                    </p>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="security_mode" value="1" {{ $config->security_mode ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                        <span class="ml-3 text-sm font-medium text-red-500 uppercase tracking-widest">{{ $config->security_mode ? 'Modo Ativo' : 'Ativar Agora' }}</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <!-- APP INFO -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fa-solid fa-mobile-screen text-netflix"></i> Informações do App
                </h3>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Nome do Aplicativo</label>
                    <input type="text" name="app_name" value="{{ old('app_name', $config->app_name) }}" required
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">TMDB API KEY</label>
                    <input type="text" name="tmdb_key" value="{{ old('tmdb_key', $config->tmdb_key) }}"
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none font-mono text-sm" placeholder="Ex: edcd52275...">
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">API Token Key (Frontend Auth)</label>
                    <input type="text" name="api_token_key" value="{{ old('api_token_key', $config->api_token_key) }}"
                           class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none font-mono text-sm">
                </div>

                <div class="space-y-4 pt-2">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="force_login" value="1" {{ $config->force_login ? 'checked' : '' }} class="w-5 h-5 accent-netflix bg-neutral-800 border-neutral-700 rounded">
                        <span class="text-sm text-neutral-300 group-hover:text-white transition">Exigir login para ver conteúdo</span>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="show_onboarding" value="1" {{ $config->show_onboarding ? 'checked' : '' }} class="w-5 h-5 accent-netflix bg-neutral-800 border-neutral-700 rounded">
                        <span class="text-sm text-neutral-300 group-hover:text-white transition">Habilitar Onboarding (Tutorial inicial)</span>
                    </label>
                </div>
            </div>

            <!-- AUTOEMBED & UPDATES -->
            <div class="space-y-8">
                <!-- AUTOEMBED -->
                <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                    <h3 class="text-white font-bold flex items-center gap-2">
                        <i class="fa-solid fa-play text-blue-500"></i> AutoEmbed Dinâmico
                    </h3>
                    <p class="text-[10px] text-neutral-500 leading-tight">Use tags como <code>{tmdb_id}</code>, <code>{season}</code> e <code>{episode}</code>.</p>

                    <div class="space-y-4">
                        <div class="bg-neutral-800/50 p-4 rounded-lg border border-neutral-700/50">
                            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                                <input type="checkbox" name="autoembed_movies" value="1" {{ $config->autoembed_movies ? 'checked' : '' }} class="w-4 h-4 accent-netflix rounded">
                                <span class="text-xs font-bold uppercase tracking-widest">Filmes</span>
                            </label>
                            <input type="text" name="autoembed_movie_url" value="{{ old('autoembed_movie_url', $config->autoembed_movie_url) }}" 
                                   class="w-full bg-neutral-900 border border-neutral-700 text-white text-xs rounded px-3 py-2 outline-none" placeholder="URL do player">
                        </div>

                        <div class="bg-neutral-800/50 p-4 rounded-lg border border-neutral-700/50">
                            <label class="flex items-center gap-3 mb-3 cursor-pointer">
                                <input type="checkbox" name="autoembed_series" value="1" {{ $config->autoembed_series ? 'checked' : '' }} class="w-4 h-4 accent-blue-500 rounded">
                                <span class="text-xs font-bold uppercase tracking-widest text-blue-500">Séries / Episódios</span>
                            </label>
                            <input type="text" name="autoembed_serie_url" value="{{ old('autoembed_serie_url', $config->autoembed_serie_url) }}" 
                                   class="w-full bg-neutral-900 border border-neutral-700 text-white text-xs rounded px-3 py-2 outline-none" placeholder="URL do player">
                        </div>
                    </div>
                </div>

                <!-- UPDATE INFO -->
                <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                    <h3 class="text-white font-bold flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-down text-green-500"></i> Atualização do App
                    </h3>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Comportamento</label>
                        <select name="update_type" class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none">
                             <option value="none" {{ $config->update_type == 'none' ? 'selected' : '' }}>APP ATUALIZADO</option>
                             <option value="in_app" {{ $config->update_type == 'in_app' ? 'selected' : '' }}>INTERNA (SILENCIOSA)</option>
                             <option value="external" {{ $config->update_type == 'external' ? 'selected' : '' }}>EXTERNA (PLAY STORE/SITES)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Link de Download/Store</label>
                        <input type="url" name="update_url" value="{{ old('update_url', $config->update_url) }}"
                               class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none text-sm font-mono">
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Version Code</label>
                            <input type="number" name="version_code" value="{{ old('version_code', $config->version_code) }}"
                                   class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none text-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Novidades da Atualização (Update Features)</label>
                            <textarea name="update_features" rows="4" placeholder="Ex: - Novo player adicionado&#10;- Correção de bugs"
                                      class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none text-sm">{{ old('update_features', $config->update_features) }}</textarea>
                        </div>
                    </div>

                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="update_skippable" value="1" {{ $config->update_skippable ? 'checked' : '' }} class="w-5 h-5 accent-red-600 bg-neutral-800 border-neutral-700 rounded">
                        <span class="text-sm text-neutral-300 group-hover:text-white transition">Permitir ignorar atualização (Opcional)</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- FOOTER ACTIONS -->
        <div class="fixed bottom-0 left-0 md:left-64 right-0 bg-neutral-900/80 backdrop-blur border-t border-neutral-800 p-4 flex justify-end z-10">
            <button type="submit" class="bg-netflix hover:bg-red-700 text-white font-bold px-10 py-3 rounded shadow-lg transition transform hover:scale-105 active:scale-95">
                SALVAR TODAS AS ALTERAÇÕES
            </button>
        </div>
    </form>
</section>
@endsection

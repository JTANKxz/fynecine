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

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- MODO SEGURANÇA -->
        <div class="grid lg:grid-cols-2 gap-8">
            <div class="bg-red-900/10 border border-red-900/30 p-6 rounded-xl">
                <div class="flex items-start gap-4">
                    <div class="bg-red-600 p-3 rounded-lg">
                        <i class="fa-solid fa-shield-halved text-white text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg mb-1 text-red-500">Modo de Segurança (Loja)</h3>
                        <p class="text-neutral-400 text-xs mb-4 leading-relaxed">
                            Oculta links de reprodução para aprovações em lojas.
                        </p>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="security_mode" value="1" {{ $config->security_mode ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                            <span class="ml-3 text-xs font-medium text-red-500 uppercase tracking-widest" id="security-mode-label">{{ $config->security_mode ? 'ATIVO' : 'DESATIVADO' }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="bg-blue-900/10 border border-blue-900/30 p-6 rounded-xl">
                <div class="flex items-start gap-4">
                    <div class="bg-blue-600 p-3 rounded-lg">
                        <i class="fa-solid fa-user-shield text-white text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-white font-bold text-lg mb-4 text-blue-400">Segurança de Rede</h3>
                        
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="block text-sm font-bold text-white mb-0.5">Bloquear VPN</span>
                                <span class="text-[10px] text-neutral-500 block leading-tight">Impede acesso via conexões VPN.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="block_vpn" value="1" {{ $config->block_vpn ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block text-sm font-bold text-white mb-0.5">Bloquear DNS Privado</span>
                                <span class="text-[10px] text-neutral-500 block leading-tight">Detecta DoH/DNS adblockers.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="block_dns" value="1" {{ $config->block_dns ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODO MANUTENÇÃO -->
        <div class="bg-orange-900/10 border border-orange-900/30 p-6 rounded-xl mt-8">
            <div class="flex items-start gap-4">
                <div class="bg-orange-600 p-3 rounded-lg">
                    <i class="fa-solid fa-screwdriver-wrench text-white text-xl"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-white font-bold text-lg mb-1 text-orange-500">Modo de Manutenção Global</h3>
                            <p class="text-neutral-400 text-xs leading-relaxed">
                                Quando ativo, todos os usuários verão apenas a tela de manutenção no aplicativo.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="maintenance_mode" value="1" {{ $config->maintenance_mode ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-14 h-7 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-orange-600"></div>
                            <span class="ml-3 text-xs font-bold text-orange-500 uppercase tracking-widest">{{ $config->maintenance_mode ? 'ATIVO' : 'DESLIGADO' }}</span>
                        </label>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Título da Manutenção</label>
                            <input type="text" name="maintenance_title" value="{{ old('maintenance_title', $config->maintenance_title) }}" placeholder="Ex: Manutenção em andamento"
                                   class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-orange-500 outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Descrição da Manutenção</label>
                            <textarea name="maintenance_description" rows="2" placeholder="Ex: Estamos realizando melhorias para sua experiência. Voltamos em breve!"
                                      class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 focus:ring-2 focus:ring-orange-500 outline-none text-sm">{{ old('maintenance_description', $config->maintenance_description) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            <div class="space-y-8">
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

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2 text-purple-400">Versão do App (Ex: 1.0.5)</label>
                        <input type="text" name="app_version" value="{{ old('app_version', $config->app_version) }}"
                               class="w-full bg-neutral-800 border border-purple-900/30 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-purple-500 outline-none text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2 text-purple-400">E-mail de Contato/Suporte</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $config->contact_email) }}"
                               class="w-full bg-neutral-800 border border-purple-900/30 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-purple-500 outline-none text-sm" placeholder="suporte@fynecine.com">
                    </div>
                </div>

                <div class="border-t border-neutral-800 pt-6">
                    <div class="flex items-center justify-between mb-2">
                         <label class="block text-xs font-bold text-neutral-500 uppercase">Mensagem Customizada (Aviso no App)</label>
                         <label class="relative inline-flex items-center cursor-pointer scale-75">
                            <input type="checkbox" name="custom_message_status" value="1" {{ $config->custom_message_status ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-netflix"></div>
                        </label>
                    </div>
                    <textarea name="custom_message" rows="3" placeholder="Ex: Manutenção agendada para às 22h..."
                              class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-netflix outline-none text-sm">{{ old('custom_message', $config->custom_message) }}</textarea>
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

            <!-- COMMENTS CONTROL -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fa-solid fa-comments text-purple-500"></i> Motor de Comentários
                </h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="block text-sm font-bold text-white mb-1">Comentários Ativos</span>
                            <span class="text-xs text-neutral-500 max-w-[200px] block leading-tight">Mestre: Ativa ou desativa a listagem e os posts no app inteiro.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer scale-75">
                            <input type="checkbox" name="comments_status" value="1" {{ $config->comments_status ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between border-t border-neutral-800 pt-4">
                        <div>
                            <span class="block text-sm font-bold text-white mb-1">Aprovação Automática</span>
                            <span class="text-xs text-neutral-500 max-w-[200px] block leading-tight">Ao desligar, novos posts ficarão ocultos aguardando liberação.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer scale-75">
                            <input type="checkbox" name="comments_auto_approve" value="1" {{ $config->comments_auto_approve ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-500"></div>
                        </label>
                    </div>
                </div>
            </div>

            </div>
            <div class="space-y-8">
                <!-- AUTOEMBED MULTI-SOURCES -->
                <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                    <h3 class="text-white font-bold flex items-center justify-between">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-play text-blue-500"></i> AutoEmbed Dinâmico</span>
                    </h3>
                    
                    <p class="text-[10px] text-neutral-500 leading-tight">Múltiplas opções de auto player. Use tags como <code>{tmdb_id}</code>, <code>{season}</code> e <code>{episode}</code>.</p>

                    <div class="space-y-8">
                        {{-- Movies Section --}}
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="autoembed_movies" value="1" {{ $config->autoembed_movies ? 'checked' : '' }} class="w-4 h-4 accent-netflix rounded">
                                    <span class="text-xs font-bold uppercase tracking-widest">Filmes</span>
                                </label>
                                <button type="button" onclick="addSource('movie')" class="text-[10px] bg-neutral-800 px-2 py-1 rounded hover:bg-neutral-700">+ ADD FONTE</button>
                            </div>
                            
                            <div id="movie-sources-container" class="space-y-3">
                                @php $movieSources = $config->autoembed_movie_sources ?? []; @endphp
                                @foreach($movieSources as $index => $source)
                                    <div class="bg-neutral-800/40 p-4 rounded-lg border border-neutral-700/30 relative group source-item">
                                        <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 bg-red-600 text-white w-5 h-5 rounded-full text-[10px] hidden group-hover:flex items-center justify-center">×</button>
                                        <input type="text" name="autoembed_movie_sources[{{$index}}][url]" value="{{ $source['url'] ?? '' }}" class="w-full bg-neutral-900 border border-neutral-700 text-white text-xs rounded px-3 py-2 outline-none mb-2" placeholder="URL do player">
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" name="autoembed_movie_sources[{{$index}}][name]" value="{{ $source['name'] ?? '' }}" placeholder="Nome" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                                            <input type="text" name="autoembed_movie_sources[{{$index}}][quality]" value="{{ $source['quality'] ?? '' }}" placeholder="Qualidade" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                                            <input type="text" name="autoembed_movie_sources[{{$index}}][type]" value="{{ $source['type'] ?? '' }}" placeholder="Tipo" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                                            <input type="text" name="autoembed_movie_sources[{{$index}}][player_sub]" value="{{ $source['player_sub'] ?? '' }}" placeholder="VIP/FREE" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Series Section --}}
                        <div class="space-y-4 border-t border-neutral-800 pt-6">
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="autoembed_series" value="1" {{ $config->autoembed_series ? 'checked' : '' }} class="w-4 h-4 accent-blue-500 rounded">
                                    <span class="text-xs font-bold uppercase tracking-widest text-blue-500">Séries / Episódios</span>
                                </label>
                                <button type="button" onclick="addSource('serie')" class="text-[10px] bg-neutral-800 px-2 py-1 rounded hover:bg-neutral-700">+ ADD FONTE</button>
                            </div>
                            
                            <div id="serie-sources-container" class="space-y-3">
                                @php $serieSources = $config->autoembed_serie_sources ?? []; @endphp
                                @foreach($serieSources as $index => $source)
                                    <div class="bg-neutral-800/40 p-4 rounded-lg border border-neutral-700/30 relative group source-item">
                                        <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 bg-red-600 text-white w-5 h-5 rounded-full text-[10px] hidden group-hover:flex items-center justify-center">×</button>
                                        <input type="text" name="autoembed_serie_sources[{{$index}}][url]" value="{{ $source['url'] ?? '' }}" class="w-full bg-neutral-900 border border-neutral-700 text-white text-xs rounded px-3 py-2 outline-none mb-2" placeholder="URL do player">
                                        <div class="grid grid-cols-2 gap-2">
                                            <input type="text" name="autoembed_serie_sources[{{$index}}][name]" value="{{ $source['name'] ?? '' }}" placeholder="Nome" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                                            <input type="text" name="autoembed_serie_sources[{{$index}}][quality]" value="{{ $source['quality'] ?? '' }}" placeholder="Qualidade" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                                            <input type="text" name="autoembed_serie_sources[{{$index}}][type]" value="{{ $source['type'] ?? '' }}" placeholder="Tipo" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                                            <input type="text" name="autoembed_serie_sources[{{$index}}][player_sub]" value="{{ $source['player_sub'] ?? '' }}" placeholder="VIP/FREE" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BUNNY CDN CONFIG -->
                <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                    <h3 class="text-white font-bold flex items-center gap-2 text-yellow-500">
                        <i class="fa-solid fa-bolt"></i> Bunny CDN (HLS / Stream)
                    </h3>
                    <p class="text-[10px] text-neutral-500 leading-tight">Configurações para o player de Stream (HLS). Formato de token no caminho.</p>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Security Key (HLS)</label>
                        <input type="password" name="bunny_security_key" value="{{ old('bunny_security_key', $config->bunny_security_key) }}" 
                               class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-yellow-500 outline-none font-mono text-sm" placeholder="Security Key para HLS">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Host CDN HLS</label>
                        <input type="text" name="bunny_cdn_url" value="{{ old('bunny_cdn_url', $config->bunny_cdn_url) }}" 
                               class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-yellow-500 outline-none text-sm font-mono" placeholder="ex: seu-hls.b-cdn.net">
                    </div>
                </div>

                <!-- BUNNY CDN MP4 CONFIG -->
                <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                    <h3 class="text-white font-bold flex items-center gap-2 text-blue-400">
                        <i class="fa-solid fa-file-video"></i> Bunny CDN (MP4 VOD)
                    </h3>
                    <p class="text-[10px] text-neutral-500 leading-tight">Configurações para vídeos MP4 diretos. Formato de token via Query String.</p>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Security Key (MP4)</label>
                        <input type="password" name="bunny_mp4_key" value="{{ old('bunny_mp4_key', $config->bunny_mp4_key) }}" 
                               class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none font-mono text-sm" placeholder="Security Key para MP4">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Host CDN MP4</label>
                        <input type="text" name="bunny_mp4_host" value="{{ old('bunny_mp4_host', $config->bunny_mp4_host) }}" 
                               class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none text-sm font-mono" placeholder="ex: seu-mp4.b-cdn.net">
                        <p class="text-[10px] text-neutral-600 mt-1">Domínio padrão para links MP4 que não possuem host completo.</p>
                    </div>
                </div>

                <!-- UPDATE INFO -->
                <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                    <h3 class="text-white font-bold flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-down text-green-500"></i> Atualização do App
                    </h3>

                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <label class="block text-xs font-bold text-neutral-500 uppercase mb-1">Status da Atualização</label>
                            <p class="text-[10px] text-neutral-500">Se desativado, o app não irá checar por novas versões</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="update_status" value="1" {{ $config->update_status ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                        </label>
                    </div>

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

        <!-- SOCIAL & LEGAL -->
        <div class="grid lg:grid-cols-2 gap-8 mt-8">
            <!-- SOCIAL MEDIA -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fa-solid fa-share-nodes text-pink-500"></i> Redes Sociais no App
                </h3>
                <p class="text-[10px] text-neutral-500 mb-4 leading-tight">Os links aparecerão no menu inferior do aplicativo caso estejam ativos.</p>

                <div class="space-y-4">
                    <!-- Instagram -->
                    <div class="bg-neutral-800/50 p-4 rounded-lg border border-neutral-700/50">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <i class="fa-brands fa-instagram text-pink-500 text-lg"></i>
                                <span class="text-xs font-bold uppercase tracking-widest text-white">Instagram</span>
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="is_instagram_active" value="1" {{ $config->is_instagram_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-pink-500"></div>
                            </label>
                        </div>
                        <input type="url" name="instagram_url" value="{{ old('instagram_url', $config->instagram_url) }}" 
                               class="w-full bg-neutral-900 border border-neutral-700 text-white text-xs rounded px-3 py-2 outline-none" placeholder="https://instagram.com/seu_perfil">
                    </div>

                    <!-- Telegram -->
                    <div class="bg-neutral-800/50 p-4 rounded-lg border border-neutral-700/50">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <i class="fa-brands fa-telegram text-blue-400 text-lg"></i>
                                <span class="text-xs font-bold uppercase tracking-widest text-white">Telegram</span>
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="is_telegram_active" value="1" {{ $config->is_telegram_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-500"></div>
                            </label>
                        </div>
                        <input type="url" name="telegram_url" value="{{ old('telegram_url', $config->telegram_url) }}" 
                               class="w-full bg-neutral-900 border border-neutral-700 text-white text-xs rounded px-3 py-2 outline-none" placeholder="https://t.me/seu_grupo">
                    </div>

                    <!-- WhatsApp -->
                    <div class="bg-neutral-800/50 p-4 rounded-lg border border-neutral-700/50">
                        <div class="flex items-center justify-between mb-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <i class="fa-brands fa-whatsapp text-green-500 text-lg"></i>
                                <span class="text-xs font-bold uppercase tracking-widest text-white">WhatsApp</span>
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="is_whatsapp_active" value="1" {{ $config->is_whatsapp_active ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                            </label>
                        </div>
                        <input type="url" name="whatsapp_url" value="{{ old('whatsapp_url', $config->whatsapp_url) }}" 
                               class="w-full bg-neutral-900 border border-neutral-700 text-white text-xs rounded px-3 py-2 outline-none" placeholder="https://wa.me/551199999999">
                    </div>
                </div>
            </div>

            <!-- REWARDS SYSTEM -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fa-solid fa-gift text-netflix"></i> Sistema de Recompensas
                </h3>
                
                <div class="flex items-center justify-between">
                    <div>
                        <span class="block text-sm font-bold text-white mb-1">Check-in Diário Ativo</span>
                        <span class="text-xs text-neutral-500 max-w-[200px] block leading-tight">Ativa ou desativa o sistema de pontos e resgate por prêmios no aplicativo móvel.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer scale-75">
                        <input type="checkbox" name="rewards_status" value="1" {{ $config->rewards_status ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-netflix"></div>
                    </label>
                </div>
            </div>

            <!-- TV CHANNELS -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fa-solid fa-tv text-blue-500"></i> Canais de TV
                </h3>
                
                <div class="flex items-center justify-between">
                    <div>
                        <span class="block text-sm font-bold text-white mb-1">Canais Ativos</span>
                        <span class="text-xs text-neutral-500 max-w-[200px] block leading-tight">Ativa ou desativa a seção de canais no aplicativo. Se desativado, o botão "Canais" será ocultado na navegação.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer scale-75">
                        <input type="checkbox" name="is_channels_active" value="1" {{ $config->is_channels_active ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-neutral-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                    </label>
                </div>
            </div>

            <!-- LEGAL DOCUMENTS -->
            <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-6">
                <h3 class="text-white font-bold flex items-center gap-2">
                    <i class="fa-solid fa-scale-balanced text-yellow-500"></i> Documentos Legais
                </h3>
                <p class="text-[10px] text-neutral-500 mb-4 leading-tight">Esses textos serão exibidos em modais dentro do aplicativo. Aceita tags básicas de formatação HTML (`<br>`, `<b>`).</p>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Termos de Uso</label>
                    <textarea name="terms_of_use" rows="7" placeholder="Insira aqui os regulamentos do app..."
                              class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-3 focus:ring-2 focus:ring-netflix outline-none text-xs leading-relaxed">{{ old('terms_of_use', $config->terms_of_use) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-2">Política de Privacidade</label>
                    <textarea name="privacy_policy" rows="7" placeholder="Insira como os dados serão armazenados..."
                              class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-3 focus:ring-2 focus:ring-netflix outline-none text-xs leading-relaxed">{{ old('privacy_policy', $config->privacy_policy) }}</textarea>
                </div>
            </div>
        </div>

        <!-- ANÚNCIOS (ADS) -->
        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl space-y-8 mt-8">
            <h3 class="text-white font-bold flex items-center gap-2">
                <i class="fa-solid fa-rectangle-ad text-yellow-500"></i> Gerenciamento de Anúncios
            </h3>

            <div class="grid lg:grid-cols-2 gap-8">
                <!-- AdMob Config -->
                <div class="space-y-6">
                    <h4 class="text-xs font-bold text-neutral-500 uppercase flex items-center gap-2">
                        <i class="fa-brands fa-google text-blue-400"></i> Configurações AdMob
                    </h4>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-neutral-400 mb-1">AdMob App ID (Android)</label>
                            <input type="text" name="admob_app_id" value="{{ $config->admob_app_id }}" placeholder="ca-app-pub-..." 
                                   class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 text-sm outline-none focus:ring-1 focus:ring-yellow-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-neutral-400 mb-1">Banner Unit ID</label>
                                <input type="text" name="admob_banner_id" value="{{ $config->admob_banner_id }}" 
                                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 text-xs outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-neutral-400 mb-1">Interstitial Unit ID</label>
                                <input type="text" name="admob_interstitial_id" value="{{ $config->admob_interstitial_id }}" 
                                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 text-xs outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-neutral-400 mb-1">Native Unit ID</label>
                                <input type="text" name="admob_native_id" value="{{ $config->admob_native_id }}" 
                                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 text-xs outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-neutral-400 mb-1">Rewarded (Premiado) ID</label>
                                <input type="text" name="admob_rewarded_id" value="{{ $config->admob_rewarded_id }}" 
                                       class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-2 text-xs outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Ads Control -->
                <div class="space-y-6">
                    <h4 class="text-xs font-bold text-neutral-500 uppercase flex items-center gap-2">
                        <i class="fa-solid fa-toggle-on text-green-500"></i> Status e Controle
                    </h4>

                    <div class="bg-neutral-800/50 p-4 rounded-lg space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block text-sm font-bold text-white">Banner Ativo</span>
                                <select name="ads_banner_type" class="bg-transparent text-[10px] text-neutral-500 outline-none">
                                    <option value="admob" {{ $config->ads_banner_type == 'admob' ? 'selected' : '' }}>ADMOB</option>
                                    <option value="custom" {{ $config->ads_banner_type == 'custom' ? 'selected' : '' }}>CUSTOM</option>
                                </select>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="ads_banner_status" value="1" {{ $config->ads_banner_status ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-neutral-700 rounded-full peer peer-checked:bg-yellow-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between border-t border-neutral-700 pt-4">
                            <div>
                                <span class="block text-sm font-bold text-white">Nativo Ativo (AdMob)</span>
                                <span class="text-[10px] text-neutral-500 block">Exibido em listas e home</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="ads_native_status" value="1" {{ $config->ads_native_status ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-neutral-700 rounded-full peer peer-checked:bg-yellow-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between border-t border-neutral-700 pt-4">
                            <div>
                                <span class="block text-sm font-bold text-white">Vídeo Premiado (Rewarded)</span>
                                <span class="text-[10px] text-neutral-500 block">Exibido ao abrir players</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="ads_rewarded_status" value="1" {{ $config->ads_rewarded_status ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-neutral-800 rounded-full peer peer-checked:bg-yellow-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between border-t border-neutral-700 pt-4">
                            <div>
                                <span class="block text-sm font-bold text-white">Interstitial Ativo</span>
                                <div class="flex items-center gap-2">
                                    <select name="ads_interstitial_type" class="bg-transparent text-[10px] text-neutral-500 outline-none">
                                        <option value="admob" {{ $config->ads_interstitial_type == 'admob' ? 'selected' : '' }}>ADMOB</option>
                                        <option value="custom" {{ $config->ads_interstitial_type == 'custom' ? 'selected' : '' }}>CUSTOM</option>
                                    </select>
                                    <span class="text-[10px] text-neutral-600">|</span>
                                    <span class="text-[10px] text-neutral-500">Intervalo:</span>
                                    <input type="number" name="interstitial_interval" value="{{ $config->interstitial_interval }}" min="1" max="20" class="bg-transparent text-[10px] text-white w-8 outline-none">
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer scale-75">
                                <input type="checkbox" name="ads_interstitial_status" value="1" {{ $config->ads_interstitial_status ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-neutral-700 rounded-full peer peer-checked:bg-yellow-500 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Ads Media -->
            <div class="border-t border-neutral-800 pt-8 grid lg:grid-cols-2 gap-8">
                <!-- Custom Banner -->
                <div class="bg-neutral-800/20 p-5 rounded-xl border border-neutral-800/50">
                    <h5 class="text-white text-sm font-bold mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-image text-neutral-500"></i> Banner Customizado
                    </h5>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-neutral-500 uppercase mb-1">Upload Imagem</label>
                                <input type="file" name="custom_banner_image_file" class="w-full text-[10px] text-neutral-400 file:bg-neutral-700 file:border-0 file:text-white file:px-2 file:py-1 file:rounded file:mr-2">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-neutral-500 uppercase mb-1">Ou URL Externa</label>
                                <input type="text" name="custom_banner_image_url" value="{{ $config->custom_banner_image && filter_var($config->custom_banner_image, FILTER_VALIDATE_URL) ? $config->custom_banner_image : '' }}" 
                                       class="w-full bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-neutral-500 uppercase mb-1">Link de Referência (Ao clicar)</label>
                            <input type="text" name="custom_banner_link" value="{{ $config->custom_banner_link }}" placeholder="https://..." 
                                   class="w-full bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1 outline-none">
                        </div>

                        @if($config->custom_banner_image)
                            <div class="mt-2">
                                <span class="text-[10px] text-neutral-500 block mb-1">Visualização:</span>
                                <img src="{{ !filter_var($config->custom_banner_image, FILTER_VALIDATE_URL) ? asset('storage/' . $config->custom_banner_image) : $config->custom_banner_image }}" class="h-12 rounded border border-neutral-700">
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Custom Interstitial -->
                <div class="bg-neutral-800/20 p-5 rounded-xl border border-neutral-800/50">
                    <h5 class="text-white text-sm font-bold mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-clapperboard text-neutral-500"></i> Interstitial Customizado
                    </h5>

                    <div class="space-y-4">
                        <div class="flex items-center gap-4 mb-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="custom_interstitial_type" value="image" {{ $config->custom_interstitial_type == 'image' ? 'checked' : '' }} class="accent-yellow-500">
                                <span class="text-[10px] text-white uppercase font-bold">Imagem</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="custom_interstitial_type" value="video" {{ $config->custom_interstitial_type == 'video' ? 'checked' : '' }} class="accent-yellow-500">
                                <span class="text-[10px] text-white uppercase font-bold">Vídeo (.mp4)</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-neutral-500 uppercase mb-1">Upload Mídia</label>
                                <input type="file" name="custom_interstitial_media_file" class="w-full text-[10px] text-neutral-400 file:bg-neutral-700 file:border-0 file:text-white file:px-2 file:py-1 file:rounded file:mr-2">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-neutral-500 uppercase mb-1">Ou URL Externa</label>
                                <input type="text" name="custom_interstitial_media_url" value="{{ $config->custom_interstitial_media && filter_var($config->custom_interstitial_media, FILTER_VALIDATE_URL) ? $config->custom_interstitial_media : '' }}" 
                                       class="w-full bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1 outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-neutral-500 uppercase mb-1">Link de Referência (Ao clicar)</label>
                            <input type="text" name="custom_interstitial_link" value="{{ $config->custom_interstitial_link }}" placeholder="https://..." 
                                   class="w-full bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1 outline-none">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER ACTIONS -->
        <div class="mt-8 bg-neutral-900 border border-neutral-800 p-4 rounded-xl flex justify-end">
            <button type="submit" class="bg-netflix hover:bg-red-700 text-white font-bold px-10 py-3 rounded shadow-lg transition transform hover:scale-105 active:scale-95 w-full md:w-auto">
                SALVAR TODAS AS ALTERAÇÕES
            </button>
        </div>
    </form>
</section>
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle label text for security mode
        const securityCheckbox = document.querySelector('input[name="security_mode"]');
        const securityLabel = document.getElementById('security-mode-label');
        
        if (securityCheckbox && securityLabel) {
            securityCheckbox.addEventListener('change', function() {
                securityLabel.textContent = this.checked ? 'MODO ATIVO' : 'ATIVAR MODO';
            });
        }
    });

    function addSource(type) {
        const container = document.getElementById(`${type}-sources-container`);
        const index = Date.now(); // Unique index for new fields
        const html = `
            <div class="bg-neutral-800 border border-purple-900/20 p-4 rounded-lg relative group source-item animate-pulse-once">
                <button type="button" onclick="this.parentElement.remove()" class="absolute -top-2 -right-2 bg-red-600 text-white w-5 h-5 rounded-full text-[10px] flex items-center justify-center shadow-lg">×</button>
                <input type="text" name="autoembed_${type}_sources[${index}][url]" class="w-full bg-neutral-900 border border-neutral-700 text-white text-xs rounded px-3 py-2 outline-none mb-2" placeholder="URL do player">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="autoembed_${type}_sources[${index}][name]" value="Auto Player" placeholder="Nome" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                    <input type="text" name="autoembed_${type}_sources[${index}][quality]" value="HD" placeholder="Qualidade" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                    <input type="text" name="autoembed_${type}_sources[${index}][type]" value="embed" placeholder="Tipo" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                    <input type="text" name="autoembed_${type}_sources[${index}][player_sub]" value="free" placeholder="VIP/FREE" class="bg-neutral-900 border border-neutral-700 text-white text-[10px] rounded px-2 py-1.5 outline-none">
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    }
</script>
<style>
    @keyframes pulse-once {
        0% { transform: scale(0.98); opacity: 0.5; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-pulse-once { animation: pulse-once 0.3s ease-out; }
</style>
@endpush
@endsection

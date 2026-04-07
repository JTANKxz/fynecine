@extends('layouts.admin')

@section('title', 'Importação em Massa de Filmes')

@section('content')
<section class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <i class="fa-solid fa-film text-netflix"></i> Importação em Massa: Filmes
        </h2>
        <a href="{{ route('admin.movies.index') }}" class="text-sm bg-neutral-800 hover:bg-neutral-700 px-4 py-2 rounded transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar
        </a>
    </div>

    <!-- Configurações Iniciais -->
    <div id="setupSection" class="bg-neutral-900 border border-neutral-800 rounded-xl p-6 mb-6 shadow-lg">
        <h3 class="text-lg font-semibold mb-4 border-b border-neutral-800 pb-2">Configurar Importação</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Iniciar de (Pular X itens)</label>
                <input type="number" id="importSkip" value="0" min="0" 
                       class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-netflix focus:outline-none transition">
                <p class="text-xs text-neutral-500 mt-1">Ex: Se já importou 500, coloque 500.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Quantidade a Importar</label>
                <input type="number" id="importQuantity" value="50" min="1" max="1000" 
                       class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-netflix focus:outline-none transition">
                <p class="text-xs text-neutral-500 mt-1">Sugestão: 50~100 por lote.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Importar Elenco?</label>
                <div class="flex items-center h-[42px]">
                    <label class="relative inline-flex items-center cursor-pointer group">
                        <input type="checkbox" id="importCast" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-neutral-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-netflix"></div>
                        <span class="ml-3 text-sm font-bold text-neutral-400 group-hover:text-white transition-colors uppercase tracking-tighter">Sim</span>
                    </label>
                </div>
            </div>
            <div class="flex items-end">
                <button id="btnFetchIds" onclick="prepareImport()" class="w-full bg-netflix hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg transition flex items-center justify-center gap-2 h-[42px]">
                    <i class="fa-solid fa-sync"></i> Iniciar Lote
                </button>
            </div>
        </div>
    </div>

    <!-- Status da Importação (Escondido initially) -->
    <div id="statusSection" class="hidden space-y-6">
        <!-- Dashboard de Progresso -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center">
                <span class="block text-xs text-neutral-500 uppercase font-bold mb-1">Total no Lote</span>
                <span id="statTotal" class="text-2xl font-bold">0</span>
            </div>
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center">
                <span class="block text-xs text-green-500 uppercase font-bold mb-1">Sucesso</span>
                <span id="statSuccess" class="text-2xl font-bold text-green-500">0</span>
            </div>
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center">
                <span class="block text-xs text-yellow-500 uppercase font-bold mb-1">Pulados</span>
                <span id="statExists" class="text-2xl font-bold text-yellow-500">0</span>
            </div>
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center">
                <span class="block text-xs text-red-500 uppercase font-bold mb-1">Falhas</span>
                <span id="statErrors" class="text-2xl font-bold text-red-500">0</span>
            </div>
        </div>

        <!-- Barra de Progresso -->
        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl relative overflow-hidden">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-3">
                    <div id="loaderSpinner" class="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-netflix"></div>
                    <span id="progressText" class="text-sm font-medium">Processando: 0%</span>
                </div>
                <div class="flex gap-2">
                    <button id="btnPause" onclick="pauseImport()" class="bg-yellow-600 hover:bg-yellow-700 text-white text-xs px-3 py-1.5 rounded transition flex items-center gap-1">
                        <i class="fa-solid fa-pause"></i> Pausar
                    </button>
                    <button id="btnResume" onclick="resumeImport()" class="hidden bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded transition flex items-center gap-1">
                        <i class="fa-solid fa-play"></i> Retomar
                    </button>
                    <button id="btnCancel" onclick="cancelImport()" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded transition flex items-center gap-1">
                        <i class="fa-solid fa-stop"></i> Cancelar
                    </button>
                </div>
            </div>
            <div class="w-full bg-neutral-800 rounded-full h-4">
                <div id="progressBar" class="bg-netflix h-4 rounded-full transition-all duration-300 shadow-[0_0_10px_rgba(139,47,255,0.5)]" style="width: 0%"></div>
            </div>
        </div>

        <!-- Logs -->
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-neutral-800 px-4 py-2 border-b border-neutral-700 flex justify-between items-center">
                <span class="text-xs font-bold uppercase tracking-wider text-neutral-400">Fluxo de Importação</span>
                <button onclick="clearLogs()" class="text-[10px] text-neutral-500 hover:text-white underline uppercase">Limpar Console</button>
            </div>
            <div id="importLogs" class="h-80 overflow-y-auto p-4 font-mono text-xs space-y-1 bg-black/40">
                <div class="text-neutral-500 italic">Aguardando sinal...</div>
            </div>
        </div>
    </div>
</section>

<style>
    #importLogs::-webkit-scrollbar { width: 4px; }
    #importLogs::-webkit-scrollbar-thumb { background: #444; border-radius: 2px; }
    .log-success { color: #10b981; border-left: 2px solid #10b981; padding-left: 8px; }
    .log-exists { color: #f59e0b; border-left: 2px solid #f59e0b; padding-left: 8px; }
    .log-error { color: #ef4444; border-left: 2px solid #ef4444; padding-left: 8px; }
    .log-info { color: #8b2fff; background: rgba(139,47,255,0.1); padding: 2px 8px; border-radius: 4px; }
</style>

@push('scripts')
<script>
    let movieQueue = [];
    let isPaused = false;
    let isCancelled = false;
    let stats = { total: 0, success: 0, exists: 0, errors: 0, processed: 0 };
    let currentTaskIndex = 0;

    async function prepareImport() {
        const skip = parseInt(document.getElementById('importSkip').value) || 0;
        const qty = parseInt(document.getElementById('importQuantity').value) || 50;
        const btnFetch = document.getElementById('btnFetchIds');
        
        btnFetch.disabled = true;
        btnFetch.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Sincronizando...';

        addLog(`Conectando à API externa para buscar lista de filmes...`, 'info');

        try {
            const response = await fetch('{{ route("admin.movies.bulk.ids") }}');
            const data = await response.json();

            if (data.success) {
                const allMovies = data.movies || [];
                addLog(`Total de filmes na API: ${allMovies.length}`, 'info');
                
                if (skip >= allMovies.length) {
                    Swal.fire('Aviso', 'O valor de "Pular" é maior ou igual ao total de filmes disponíveis.', 'warning');
                    resetBtn();
                    return;
                }

                movieQueue = allMovies.slice(skip, skip + qty);
                stats.total = movieQueue.length;
                stats.processed = 0;
                stats.success = 0;
                stats.exists = 0;
                stats.errors = 0;
                currentTaskIndex = 0;
                
                document.getElementById('setupSection').classList.add('hidden');
                document.getElementById('statusSection').classList.remove('hidden');
                document.getElementById('statTotal').innerText = stats.total;
                
                addLog(`Iniciando lote: Pulando ${skip} e processando ${movieQueue.length} itens.`);
                startImport();
            } else {
                Swal.fire('Erro', data.error || 'Erro ao buscar IDs', 'error');
                resetBtn();
            }
        } catch (error) {
            Swal.fire('Erro Fatal', error.message, 'error');
            resetBtn();
        }
    }

    function resetBtn() {
        const btnFetch = document.getElementById('btnFetchIds');
        btnFetch.disabled = false;
        btnFetch.innerHTML = '<i class="fa-solid fa-sync"></i> Iniciar Processo';
    }

    async function startImport() {
        isPaused = false;
        isCancelled = false;
        processNext();
    }

    async function processNext() {
        if (isPaused || isCancelled || currentTaskIndex >= movieQueue.length) {
            if (currentTaskIndex >= movieQueue.length) {
                finishImport();
            }
            return;
        }

        const movie = movieQueue[currentTaskIndex];
        const tmdbId = movie.tmdb_id;
        
        addLog(`Item ${currentTaskIndex + 1}/${movieQueue.length} | TMDB ID: ${tmdbId}`, 'info');

        const importCast = document.getElementById('importCast').checked;

        try {
            const response = await fetch('{{ route("admin.movies.bulk.import") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ 
                    tmdb_id: tmdbId,
                    import_cast: importCast
                })
            });

            const result = await response.json();

            if (result.success) {
                if (result.status === 'imported') {
                    stats.success++;
                    addLog(`[IMPORTADO] ${result.movie.title}`, 'success');
                } else if (result.status === 'exists') {
                    stats.exists++;
                    addLog(`[PULADO] ${result.message}`, 'exists');
                }
            } else {
                stats.errors++;
                addLog(`[FALHA] TMDB ${tmdbId}: ${result.error}`, 'error');
            }
        } catch (error) {
            stats.errors++;
            addLog(`[CRÍTICO] TMDB ${tmdbId}: ${error.message}`, 'error');
        }

        stats.processed++;
        updateUI();
        currentTaskIndex++;
        
        // Pequena pausa para evitar sobrecarga
        setTimeout(processNext, 600);
    }

    function updateUI() {
        const percent = Math.round((stats.processed / stats.total) * 100);
        document.getElementById('progressBar').style.width = percent + '%';
        document.getElementById('progressText').innerText = `Progresso: ${percent}% (${stats.processed}/${stats.total})`;
        document.getElementById('statSuccess').innerText = stats.success;
        document.getElementById('statExists').innerText = stats.exists;
        document.getElementById('statErrors').innerText = stats.errors;
    }

    function addLog(message, type = '') {
        const logContainer = document.getElementById('importLogs');
        const now = new Date().toLocaleTimeString();
        const logItem = document.createElement('div');
        logItem.className = type ? `log-${type}` : 'text-neutral-300';
        logItem.innerHTML = `<span class="text-neutral-500 font-normal">[${now}]</span> ${message}`;
        logContainer.appendChild(logItem);
        logContainer.scrollTop = logContainer.scrollHeight;
    }

    function clearLogs() {
        document.getElementById('importLogs').innerHTML = '<div class="text-neutral-500 italic text-[10px]">Console limpo em ' + new Date().toLocaleTimeString() + '</div>';
    }

    function pauseImport() {
        isPaused = true;
        document.getElementById('btnPause').classList.add('hidden');
        document.getElementById('btnResume').classList.remove('hidden');
        document.getElementById('loaderSpinner').classList.add('hidden');
        addLog('PROCESSO PAUSADO.', 'exists');
    }

    function resumeImport() {
        isPaused = false;
        document.getElementById('btnPause').classList.remove('hidden');
        document.getElementById('btnResume').classList.add('hidden');
        document.getElementById('loaderSpinner').classList.remove('hidden');
        addLog('PROCESSO RETOMADO.', 'success');
        processNext();
    }

    function cancelImport() {
        Swal.fire({
            title: 'Cancelar?',
            text: "O progresso atual deste lote será perdido.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#8b2fff',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Interromper',
            cancelButtonText: 'Continuar'
        }).then((result) => {
            if (result.isConfirmed) {
                isCancelled = true;
                addLog('IMPORTAÇÃO CANCELADA.', 'error');
                document.getElementById('loaderSpinner').classList.add('hidden');
                document.getElementById('btnPause').disabled = true;
                document.getElementById('btnResume').disabled = true;
                document.getElementById('btnCancel').disabled = true;
            }
        });
    }

    function finishImport() {
        document.getElementById('loaderSpinner').classList.add('hidden');
        document.getElementById('btnPause').disabled = true;
        document.getElementById('btnResume').disabled = true;
        document.getElementById('btnCancel').disabled = true;
        addLog('🏁 PROCESSO FINALIZADO!', 'info');
        
        Swal.fire({
            title: 'Fim do Lote!',
            html: `Sincronização concluída.<br><b>Sucesso:</b> ${stats.success}<br><b>Pulados:</b> ${stats.exists}<br><b>Erros:</b> ${stats.errors}`,
            icon: 'success',
            confirmButtonColor: '#8b2fff'
        });
    }
</script>
@endpush
@endsection

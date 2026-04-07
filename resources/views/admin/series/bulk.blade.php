@extends('layouts.admin')

@section('title', 'Importação em Massa de Séries')

@section('content')
<section class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <i class="fa-solid fa-tv text-netflix"></i> Importação em Massa: Séries
        </h2>
        <a href="{{ route('admin.series.index') }}" class="text-sm bg-neutral-800 hover:bg-neutral-700 px-4 py-2 rounded transition">
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
                <p class="text-xs text-neutral-500 mt-1">Ex: Se já importou 300, coloque 300.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Quantidade a Importar</label>
                <input type="number" id="importQuantity" value="30" min="1" max="500" 
                       class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-netflix focus:outline-none transition">
                <p class="text-xs text-neutral-500 mt-1">Para séries, 30 por lote é mais seguro.</p>
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
        <div class="mt-4 p-3 bg-neutral-800/50 rounded border border-neutral-700">
            <p class="text-[10px] text-neutral-400 leading-relaxed uppercase tracking-tighter">
                <i class="fa-solid fa-circle-info mr-1 text-netflix"></i> ATENÇÃO: A importação de séries é mais lenta pois processa temporadas e episódios.
            </p>
        </div>
    </div>

    <!-- Status da Importação (Escondido initially) -->
    <div id="statusSection" class="hidden space-y-6">
        <!-- Dashboard de Progresso -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center shadow-lg">
                <span class="block text-xs text-neutral-500 uppercase font-bold mb-1">Planejado</span>
                <span id="statTotal" class="text-2xl font-bold">0</span>
            </div>
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center shadow-lg">
                <span class="block text-xs text-green-500 uppercase font-bold mb-1">Importadas</span>
                <span id="statSuccess" class="text-2xl font-bold text-green-500">0</span>
            </div>
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center shadow-lg">
                <span class="block text-xs text-yellow-500 uppercase font-bold mb-1">Existentes</span>
                <span id="statExists" class="text-2xl font-bold text-yellow-500">0</span>
            </div>
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center shadow-lg">
                <span class="block text-xs text-red-500 uppercase font-bold mb-1">Erros</span>
                <span id="statErrors" class="text-2xl font-bold text-red-500">0</span>
            </div>
        </div>

        <!-- Barra de Progresso -->
        <div class="bg-neutral-900 border border-neutral-800 p-6 rounded-xl relative overflow-hidden shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-3">
                    <div id="loaderSpinner" class="animate-spin rounded-full h-5 w-5 border-t-2 border-b-2 border-netflix"></div>
                    <span id="progressText" class="text-sm font-medium">Sincronizando: 0%</span>
                </div>
                <div class="flex gap-2">
                    <button id="btnPause" onclick="pauseImport()" class="bg-yellow-600 hover:bg-yellow-700 text-white text-xs px-3 py-1.5 rounded transition flex items-center gap-1">
                        <i class="fa-solid fa-pause"></i> Pausar
                    </button>
                    <button id="btnResume" onclick="resumeImport()" class="hidden bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded transition flex items-center gap-1">
                        <i class="fa-solid fa-play"></i> Retomar
                    </button>
                    <button id="btnCancel" onclick="cancelImport()" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded transition flex items-center gap-1">
                        <i class="fa-solid fa-stop"></i> Parar
                    </button>
                </div>
            </div>
            <div class="w-full bg-neutral-800 rounded-full h-4">
                <div id="progressBar" class="bg-netflix h-4 rounded-full transition-all duration-300 shadow-[0_0_12px_rgba(139,47,255,0.6)]" style="width: 0%"></div>
            </div>
        </div>

        <!-- Logs -->
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl overflow-hidden shadow-2xl">
            <div class="bg-neutral-800 px-4 py-2 border-b border-neutral-700 flex justify-between items-center">
                <span class="text-xs font-bold uppercase tracking-wider text-neutral-400">Terminal de Sincronização</span>
                <button onclick="clearLogs()" class="text-[10px] text-neutral-500 hover:text-white underline uppercase">Limpar Console</button>
            </div>
            <div id="importLogs" class="h-80 overflow-y-auto p-4 font-mono text-[11px] space-y-1 bg-black/50">
                <div class="text-neutral-500 italic">Aguardando sinal para iniciar...</div>
            </div>
        </div>
    </div>
</section>

<style>
    #importLogs::-webkit-scrollbar { width: 4px; }
    #importLogs::-webkit-scrollbar-thumb { background: #333; border-radius: 2px; }
    .log-success { color: #10b981; border-left: 3px solid #10b981; padding-left: 8px; }
    .log-exists { color: #f59e0b; border-left: 3px solid #f59e0b; padding-left: 8px; }
    .log-error { color: #ef4444; border-left: 3px solid #ef4444; padding-left: 8px; }
    .log-info { color: #8b2fff; background: rgba(139,47,255,0.05); padding: 2px 8px; border-radius: 4px; }
</style>

@push('scripts')
<script>
    let seriesQueue = [];
    let isPaused = false;
    let isCancelled = false;
    let stats = { total: 0, success: 0, exists: 0, errors: 0, processed: 0 };
    let currentTaskIndex = 0;

    async function prepareImport() {
        const skip = parseInt(document.getElementById('importSkip').value) || 0;
        const qty = parseInt(document.getElementById('importQuantity').value) || 30;
        const btnFetch = document.getElementById('btnFetchIds');
        
        btnFetch.disabled = true;
        btnFetch.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Sincronizando...';

        addLog(`Buscando lista completa de séries na API externa...`, 'info');

        try {
            const response = await fetch('{{ route("admin.series.bulk.ids") }}');
            const data = await response.json();

            if (data.success) {
                const allSeries = data.series || [];
                addLog(`Total de séries encontradas: ${allSeries.length}`, 'info');
                
                if (skip >= allSeries.length) {
                    Swal.fire('Aviso', 'O valor de "Pular" é maior que o total de séries.', 'warning');
                    resetBtn();
                    return;
                }

                seriesQueue = allSeries.slice(skip, skip + qty);
                stats.total = seriesQueue.length;
                stats.processed = 0; stats.success = 0; stats.exists = 0; stats.errors = 0;
                currentTaskIndex = 0;
                
                document.getElementById('setupSection').classList.add('hidden');
                document.getElementById('statusSection').classList.remove('hidden');
                document.getElementById('statTotal').innerText = stats.total;
                
                addLog(`Iniciando Batch: Pulando ${skip} e processando ${seriesQueue.length} séries.`);
                startImport();
            } else {
                Swal.fire('Erro', data.error || 'Erro ao buscar dados', 'error');
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
        btnFetch.innerHTML = '<i class="fa-solid fa-sync"></i> Iniciar Lote';
    }

    async function startImport() {
        isPaused = false;
        isCancelled = false;
        processNext();
    }

    async function processNext() {
        if (isPaused || isCancelled || currentTaskIndex >= seriesQueue.length) {
            if (currentTaskIndex >= seriesQueue.length) finishImport();
            return;
        }

        const serie = seriesQueue[currentTaskIndex];
        const tmdbId = serie.tmdb_id;
        
        addLog(`[${currentTaskIndex + 1}/${seriesQueue.length}] Analisando TMDB ID: ${tmdbId}...`, 'info');

        const importCast = document.getElementById('importCast').checked;

        try {
            const response = await fetch('{{ route("admin.series.bulk.import") }}', {
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
                    addLog(`[OK] ${result.series.name} - Sincronizada com temporadas/episódios.`, 'success');
                } else if (result.status === 'exists') {
                    stats.exists++;
                    addLog(`[PULO] ${result.message}`, 'exists');
                }
            } else {
                stats.errors++;
                addLog(`[ERRO] TMDB ${tmdbId}: ${result.error}`, 'error');
            }
        } catch (error) {
            stats.errors++;
            addLog(`[CRÍTICO] TMDB ${tmdbId}: ${error.message}`, 'error');
        }

        stats.processed++;
        updateUI();
        currentTaskIndex++;
        
        // Séries demoram mais, delay de 1.2s para segurança
        setTimeout(processNext, 1200);
    }

    function updateUI() {
        const percent = Math.round((stats.processed / stats.total) * 100);
        document.getElementById('progressBar').style.width = percent + '%';
        document.getElementById('progressText').innerText = `Sincronizando: ${percent}% (${stats.processed}/${stats.total})`;
        document.getElementById('statSuccess').innerText = stats.success;
        document.getElementById('statExists').innerText = stats.exists;
        document.getElementById('statErrors').innerText = stats.errors;
    }

    function addLog(message, type = '') {
        const logContainer = document.getElementById('importLogs');
        const now = new Date().toLocaleTimeString();
        const logItem = document.createElement('div');
        logItem.className = type ? `log-${type}` : 'text-neutral-300';
        logItem.innerHTML = `<span class="text-neutral-600">[${now}]</span> ${message}`;
        logContainer.appendChild(logItem);
        logContainer.scrollTop = logContainer.scrollHeight;
    }

    function clearLogs() {
        document.getElementById('importLogs').innerHTML = '<div class="text-neutral-500 italic">Console limpo.</div>';
    }

    function pauseImport() {
        isPaused = true;
        document.getElementById('btnPause').classList.add('hidden');
        document.getElementById('btnResume').classList.remove('hidden');
        document.getElementById('loaderSpinner').classList.add('hidden');
        addLog('PROCESSO PAUSADO PELO USUÁRIO.', 'exists');
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
            title: 'Parar Sincronização?',
            text: "O processo será interrompido permanentemente para este lote.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#8b2fff',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Parar Agora',
            cancelButtonText: 'Continuar'
        }).then((result) => {
            if (result.isConfirmed) {
                isCancelled = true;
                addLog('IMPORTAÇÃO ABORTADA.', 'error');
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
        addLog('🏁 PROCESSO CONCLUÍDO!', 'info');
        
        Swal.fire({
            title: 'Batch Concluído!',
            html: `Séries sincronizadas com sucesso.<br><br>Sucesso: ${stats.success}<br>Erros: ${stats.errors}`,
            icon: 'success',
            confirmButtonColor: '#8b2fff'
        });
    }
</script>
@endpush
@endsection

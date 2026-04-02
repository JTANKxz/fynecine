@extends('layouts.admin')

@section('title', 'Importação em Massa de Filmes')

@section('content')
<section class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <i class="fa-solid fa-cloud-arrow-down text-netflix"></i> Importação em Massa
        </h2>
        <a href="{{ route('admin.movies.index') }}" class="text-sm bg-neutral-800 hover:bg-neutral-700 px-4 py-2 rounded transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Voltar
        </a>
    </div>

    <!-- Configurações Iniciais -->
    <div id="setupSection" class="bg-neutral-900 border border-neutral-800 rounded-xl p-6 mb-6 shadow-lg">
        <h3 class="text-lg font-semibold mb-4 border-b border-neutral-800 pb-2">Configurar Importação</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-neutral-400 mb-2">Quantidade de Itens</label>
                <input type="number" id="importQuantity" value="50" min="1" max="1000" 
                       class="w-full bg-neutral-800 border border-neutral-700 rounded-lg px-4 py-2 focus:ring-2 focus:ring-netflix focus:outline-none transition">
                <p class="text-xs text-neutral-500 mt-1">Limite recomendado: 500 por vez para evitar bloqueios.</p>
            </div>
            <div class="flex items-end">
                <button id="btnFetchIds" onclick="prepareImport()" class="w-full bg-netflix hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-sync"></i> Carregar IDs da API
                </button>
            </div>
        </div>
    </div>

    <!-- Status da Importação (Escondido initially) -->
    <div id="statusSection" class="hidden space-y-6">
        <!-- Dashboard de Progresso -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center">
                <span class="block text-xs text-neutral-500 uppercase font-bold mb-1">Total</span>
                <span id="statTotal" class="text-2xl font-bold">0</span>
            </div>
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center">
                <span class="block text-xs text-green-500 uppercase font-bold mb-1">Sucesso</span>
                <span id="statSuccess" class="text-2xl font-bold text-green-500">0</span>
            </div>
            <div class="bg-neutral-900 border border-neutral-800 p-4 rounded-xl text-center">
                <span class="block text-xs text-yellow-500 uppercase font-bold mb-1">Já Existe</span>
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
        <div class="bg-neutral-900 border border-neutral-800 rounded-xl overflow-hidden">
            <div class="bg-neutral-800 px-4 py-2 border-b border-neutral-700 flex justify-between items-center">
                <span class="text-xs font-bold uppercase tracking-wider text-neutral-400">Log de Atividades</span>
                <button onclick="clearLogs()" class="text-[10px] text-neutral-500 hover:text-white underline">Limpar Logs</button>
            </div>
            <div id="importLogs" class="h-64 overflow-y-auto p-4 font-mono text-sm space-y-1 bg-black/30">
                <div class="text-neutral-500 italic">Aguardando início...</div>
            </div>
        </div>
    </div>
</section>

<style>
    #importLogs::-webkit-scrollbar { width: 4px; }
    #importLogs::-webkit-scrollbar-thumb { background: #333; border-radius: 2px; }
    .log-success { color: #10b981; }
    .log-exists { color: #f59e0b; }
    .log-error { color: #ef4444; }
    .log-info { color: #8b2fff; }
</style>

@push('scripts')
<script>
    let movieQueue = [];
    let isPaused = false;
    let isCancelled = false;
    let stats = { total: 0, success: 0, exists: 0, errors: 0, processed: 0 };
    let currentTaskIndex = 0;

    async function prepareImport() {
        const qty = document.getElementById('importQuantity').value;
        const btnFetch = document.getElementById('btnFetchIds');
        
        btnFetch.disabled = true;
        btnFetch.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Carregando IDs...';

        addLog('Iniciando busca de IDs na API externa...', 'info');

        try {
            const response = await fetch('{{ route("admin.movies.bulk.ids") }}');
            const data = await response.json();

            if (data.success) {
                movieQueue = data.movies.slice(0, qty);
                stats.total = movieQueue.length;
                
                document.getElementById('setupSection').classList.add('hidden');
                document.getElementById('statusSection').classList.remove('hidden');
                document.getElementById('statTotal').innerText = stats.total;
                
                addLog(`Sucesso: ${data.movies.length} IDs encontrados. Importando os primeiros ${movieQueue.length} itens.`);
                startImport();
            } else {
                Swal.fire('Erro', data.error || 'Erro ao buscar IDs', 'error');
                btnFetch.disabled = false;
                btnFetch.innerHTML = '<i class="fa-solid fa-sync"></i> Carregar IDs da API';
            }
        } catch (error) {
            Swal.fire('Erro Fatal', error.message, 'error');
            btnFetch.disabled = false;
            btnFetch.innerHTML = '<i class="fa-solid fa-sync"></i> Carregar IDs da API';
        }
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
        
        addLog(`[${currentTaskIndex + 1}/${movieQueue.length}] Processando TMDB: ${tmdbId}...`);

        try {
            const response = await fetch('{{ route("admin.movies.bulk.import") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ tmdb_id: tmdbId })
            });

            const result = await response.json();

            if (result.success) {
                if (result.status === 'imported') {
                    stats.success++;
                    addLog(`[OK] ${result.movie.title} importado com sucesso.`, 'success');
                } else if (result.status === 'exists') {
                    stats.exists++;
                    addLog(`[SKIP] ${result.message}`, 'exists');
                }
            } else {
                stats.errors++;
                addLog(`[ERROR] ID ${tmdbId}: ${result.error}`, 'error');
            }
        } catch (error) {
            stats.errors++;
            addLog(`[CRITICAL] ID ${tmdbId}: ${error.message}`, 'error');
        }

        stats.processed++;
        updateUI();
        currentTaskIndex++;
        
        // Pequeno delay para respirar e dar tempo da UI atualizar suavemente
        setTimeout(processNext, 500);
    }

    function updateUI() {
        const percent = Math.round((stats.processed / stats.total) * 100);
        document.getElementById('progressBar').style.width = percent + '%';
        document.getElementById('progressText').innerText = `Processando: ${percent}% (${stats.processed}/${stats.total})`;
        document.getElementById('statSuccess').innerText = stats.success;
        document.getElementById('statExists').innerText = stats.exists;
        document.getElementById('statErrors').innerText = stats.errors;
    }

    function addLog(message, type = '') {
        const logContainer = document.getElementById('importLogs');
        const now = new Date().toLocaleTimeString();
        const logItem = document.createElement('div');
        logItem.className = type ? `log-${type}` : 'text-neutral-300';
        logItem.innerHTML = `<span class="text-neutral-500">[${now}]</span> ${message}`;
        logContainer.appendChild(logItem);
        logContainer.scrollTop = logContainer.scrollHeight;
    }

    function clearLogs() {
        document.getElementById('importLogs').innerHTML = '';
    }

    function pauseImport() {
        isPaused = true;
        document.getElementById('btnPause').classList.add('hidden');
        document.getElementById('btnResume').classList.remove('hidden');
        document.getElementById('loaderSpinner').classList.add('hidden');
        addLog('Importação pausada pelo usuário.', 'info');
    }

    function resumeImport() {
        isPaused = false;
        document.getElementById('btnPause').classList.remove('hidden');
        document.getElementById('btnResume').classList.add('hidden');
        document.getElementById('loaderSpinner').classList.remove('hidden');
        addLog('Importação retomada.', 'info');
        processNext();
    }

    function cancelImport() {
        Swal.fire({
            title: 'Cancelar Importação?',
            text: "O processo será interrompido permanentemente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#8b2fff',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, cancelar',
            cancelButtonText: 'Não, continuar'
        }).then((result) => {
            if (result.isConfirmed) {
                isCancelled = true;
                addLog('IMPORTAÇÃO CANCELADA PELO USUÁRIO.', 'error');
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
        addLog('=======================================', 'info');
        addLog('PROCESSO CONCLUÍDO!', 'info');
        addLog(`Total: ${stats.total} | Sucesso: ${stats.success} | Já Existente: ${stats.exists} | Falhas: ${stats.errors}`, 'info');

        Swal.fire({
            title: 'Importação Concluída!',
            text: `${stats.success} filmes foram adicionados.`,
            icon: 'success',
            confirmButtonColor: '#8b2fff'
        });
    }
</script>
@endpush
@endsection

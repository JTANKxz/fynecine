<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Pagamento PIX</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            background: #000;
            color: #fff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 24px 16px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header .plan-badge {
            display: inline-block;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .header .price {
            font-size: 28px;
            font-weight: 900;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header .duration {
            font-size: 13px;
            color: #888;
            margin-top: 2px;
        }

        .qr-container {
            background: #fff;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 40px rgba(124, 58, 237, 0.2);
        }

        .qr-container img {
            width: 220px;
            height: 220px;
            display: block;
        }

        .copy-section {
            width: 100%;
            max-width: 360px;
            margin-bottom: 20px;
        }

        .copy-section label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            text-align: center;
        }

        .copy-box {
            display: flex;
            background: #111;
            border: 1px solid #333;
            border-radius: 12px;
            overflow: hidden;
        }

        .copy-box input {
            flex: 1;
            background: transparent;
            border: none;
            color: #ccc;
            padding: 14px 16px;
            font-size: 11px;
            font-family: 'Courier New', monospace;
            outline: none;
            min-width: 0;
        }

        .copy-box button {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: #fff;
            border: none;
            padding: 14px 20px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            transition: opacity 0.2s;
        }

        .copy-box button:active {
            opacity: 0.7;
        }

        .copy-box button.copied {
            background: #16a34a;
        }

        .timer-section {
            text-align: center;
            margin-bottom: 24px;
        }

        .timer-section .label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .timer-section .timer {
            font-size: 32px;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            color: #fff;
        }

        .timer-section .timer.warning {
            color: #f59e0b;
        }

        .timer-section .timer.danger {
            color: #ef4444;
        }

        .status-section {
            width: 100%;
            max-width: 360px;
            text-align: center;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
        }

        .status-badge.pending {
            background: #1e1b4b;
            color: #a78bfa;
            border: 1px solid #4c1d95;
        }

        .status-badge.approved {
            background: #052e16;
            color: #4ade80;
            border: 1px solid #166534;
        }

        .status-badge.expired {
            background: #1c1917;
            color: #a8a29e;
            border: 1px solid #44403c;
        }

        .spinner {
            width: 14px;
            height: 14px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .success-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.95);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 24px;
        }

        .success-overlay.active {
            display: flex;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #16a34a, #22c55e);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            animation: pop 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        @keyframes pop {
            0% { transform: scale(0); }
            100% { transform: scale(1); }
        }

        .success-icon svg {
            width: 40px;
            height: 40px;
            fill: none;
            stroke: #fff;
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .success-overlay h2 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .success-overlay p {
            color: #888;
            font-size: 14px;
            text-align: center;
            max-width: 280px;
            line-height: 1.5;
        }

        .instructions {
            width: 100%;
            max-width: 360px;
            margin-top: 8px;
            padding: 16px;
            background: #111;
            border: 1px solid #222;
            border-radius: 12px;
        }

        .instructions h3 {
            font-size: 12px;
            font-weight: 700;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .instructions ol {
            padding-left: 20px;
        }

        .instructions li {
            color: #aaa;
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="plan-badge">{{ $plan->plan_type }}</div>
        <h1>{{ $plan->name }}</h1>
        <div class="price">R$ {{ number_format($payment->amount, 2, ',', '.') }}</div>
        <div class="duration">{{ $plan->duration_days }} dias de acesso</div>
    </div>

    <!-- QR Code -->
    @if($payment->pix_qr_code_base64)
    <div class="qr-container">
        <img src="data:image/png;base64,{{ $payment->pix_qr_code_base64 }}" alt="QR Code PIX">
    </div>
    @endif

    <!-- Copia e Cola -->
    @if($payment->pix_qr_code)
    <div class="copy-section">
        <label>Pix Copia e Cola</label>
        <div class="copy-box">
            <input type="text" id="pixCode" value="{{ $payment->pix_qr_code }}" readonly>
            <button id="copyBtn" onclick="copyCode()">Copiar</button>
        </div>
    </div>
    @endif

    <!-- Timer -->
    <div class="timer-section">
        <div class="label">Expira em</div>
        <div class="timer" id="timer">--:--</div>
    </div>

    <!-- Status -->
    <div class="status-section">
        <div class="status-badge pending" id="statusBadge">
            <div class="spinner"></div>
            <span id="statusText">Aguardando pagamento...</span>
        </div>
    </div>

    <!-- Instruções -->
    <div class="instructions">
        <h3>Como pagar</h3>
        <ol>
            <li>Abra o app do seu banco ou carteira digital</li>
            <li>Escolha pagar com <strong>PIX</strong></li>
            <li>Escaneie o QR Code ou cole o código</li>
            <li>Confirme o pagamento</li>
        </ol>
    </div>

    <!-- Success Overlay -->
    <div class="success-overlay" id="successOverlay">
        <div class="success-icon">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <h2>Pagamento Confirmado!</h2>
        <p>Seu plano <strong>{{ $plan->name }}</strong> foi ativado com sucesso. Aproveite!</p>
    </div>

    <script>
        const paymentId = {{ $payment->id }};
        const expiresAt = new Date("{{ $payment->expires_at->toISOString() }}");
        let pollInterval = null;

        // === Timer ===
        function updateTimer() {
            const now = new Date();
            const diff = expiresAt - now;

            if (diff <= 0) {
                document.getElementById('timer').textContent = '00:00';
                document.getElementById('timer').className = 'timer danger';
                document.getElementById('statusBadge').className = 'status-badge expired';
                document.getElementById('statusBadge').innerHTML = '<span>PIX expirado</span>';
                clearInterval(pollInterval);
                return;
            }

            const minutes = Math.floor(diff / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            const timeStr = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            const timerEl = document.getElementById('timer');
            timerEl.textContent = timeStr;
            
            if (minutes < 5) {
                timerEl.className = 'timer danger';
            } else if (minutes < 10) {
                timerEl.className = 'timer warning';
            }
        }

        // === Copy ===
        function copyCode() {
            const input = document.getElementById('pixCode');
            input.select();
            input.setSelectionRange(0, 99999);
            
            navigator.clipboard.writeText(input.value).then(() => {
                const btn = document.getElementById('copyBtn');
                btn.textContent = '✓ Copiado';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = 'Copiar';
                    btn.classList.remove('copied');
                }, 2000);
            }).catch(() => {
                document.execCommand('copy');
            });
        }

        // === Polling ===
        function checkStatus() {
            fetch(`/api/pix/status/${paymentId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'approved') {
                        clearInterval(pollInterval);
                        showSuccess();
                    } else if (data.status === 'rejected' || data.status === 'cancelled') {
                        clearInterval(pollInterval);
                        document.getElementById('statusBadge').className = 'status-badge expired';
                        document.getElementById('statusBadge').innerHTML = '<span>Pagamento não aprovado</span>';
                    }
                })
                .catch(() => { /* Silently retry on next interval */ });
        }

        function showSuccess() {
            document.getElementById('successOverlay').classList.add('active');
            
            // Tenta redirecionar via deep link após 2s
            setTimeout(() => {
                window.location.href = 'fynecine://payment-success';
            }, 2500);
        }

        // === Init ===
        updateTimer();
        setInterval(updateTimer, 1000);
        pollInterval = setInterval(checkStatus, 5000);

        // Check imediato
        checkStatus();
    </script>

</body>
</html>

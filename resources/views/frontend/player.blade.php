<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="referrer" content="no-referrer">
    <title>Assistir {{ ucwords(str_replace('-', ' ', $slug)) }} - FYNECINE</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #000000;
            overflow: hidden;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .player-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
            position: absolute;
            top: 0;
            left: 0;
            z-index: 2;
        }
        .loading {
            position: absolute;
            z-index: 1;
            color: #7c3aed;
            font-size: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(124, 58, 237, 0.2);
            border-top-color: #7c3aed;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="player-container">
        <div class="loading">
            <div class="spinner"></div>
            <span style="font-size: 14px; color: #a0a0a0;">Carregando Player...</span>
        </div>
        <iframe src="{{ $url }}" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" scrolling="no"></iframe>
    </div>
</body>
</html>

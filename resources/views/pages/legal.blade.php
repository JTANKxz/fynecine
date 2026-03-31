<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f0f0f;
            color: #ffffff;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            color: #a855f7; /* Purple 500 */
            border-bottom: 2px solid #a855f7;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .content {
            white-space: pre-line;
            font-size: 14px;
            color: #cccccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ $title }}</h1>
        <div class="content">
            {!! nl2br(e($content)) !!}
        </div>
    </div>
</body>
</html>

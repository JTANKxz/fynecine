<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #0c0c0c; color: #fff; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #141414; padding: 40px; border: 1px solid #333; border-radius: 8px; text-align: center; }
        h1 { color: #e50914; font-size: 24px; margin-bottom: 20px; }
        p { color: #ccc; font-size: 16px; line-height: 1.5; }
        .code-box { display: inline-block; margin: 30px 0; padding: 20px; border: 1px dashed #e50914; border-radius: 4px; background: #222; color: #fff; font-size: 42px; font-weight: bold; letter-spacing: 15px; }
        .footer { margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        @if (($purpose ?? 'reset') === 'activation')
            <h1>Ative sua conta</h1>
            <p>Ol?,</p>
            <p>Use o c?digo abaixo para confirmar sua compra e criar sua conta no <strong>{{ config('app.name') }}</strong>.</p>
            <div class="code-box">{{ $code }}</div>
            <p>Este c?digo expira em 15 minutos. Se voc? n?o fez esta compra, ignore este e-mail.</p>
        @else
            <h1>Recupera??o de senha</h1>
            <p>Ol?,</p>
            <p>Recebemos uma solicita??o para redefinir a senha da sua conta no <strong>{{ config('app.name') }}</strong>.</p>
            <p>Use o c?digo abaixo para prosseguir com a redefini??o:</p>
            <div class="code-box">{{ $code }}</div>
            <p>Este c?digo expira em 15 minutos. Se voc? n?o solicitou isso, ignore este e-mail.</p>
        @endif
        <div class="footer">&copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</div>
    </div>
</body>
</html>

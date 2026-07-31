<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #0c0c0c; color: #fff; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #141414; padding: 40px; border: 1px solid #333; border-radius: 8px; text-align: center; }
        h1 { color: #8b5cf6; font-size: 24px; margin-bottom: 20px; }
        p { color: #ccc; font-size: 16px; line-height: 1.5; }
        .code-box { display: inline-block; margin: 30px 0; padding: 20px; border: 1px dashed #8b5cf6; border-radius: 8px; background: #222; color: #fff; font-size: 42px; font-weight: bold; letter-spacing: 15px; }
        .footer { margin-top: 30px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        @if (($purpose ?? 'reset') === 'activation')
            <h1>Ative sua conta</h1>
            <p>Olá,</p>
            <p>Use o código abaixo para confirmar sua compra e criar sua conta no <strong>{{ config('app.name') }}</strong>.</p>
            <div class="code-box">{{ $code }}</div>
            <p>Este código expira em 15 minutos. Se você não fez esta compra, ignore este e-mail.</p>
        @else
            <h1>Recuperação de senha</h1>
            <p>Olá,</p>
            <p>Recebemos uma solicitação para redefinir a senha da sua conta no <strong>{{ config('app.name') }}</strong>.</p>
            <p>Use o código abaixo para prosseguir com a redefinição:</p>
            <div class="code-box">{{ $code }}</div>
            <p>Este código expira em 15 minutos. Se você não solicitou isso, ignore este e-mail.</p>
        @endif
        <div class="footer">&copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.</div>
    </div>
</body>
</html>
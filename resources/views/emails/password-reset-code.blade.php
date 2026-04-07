<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #0c0c0c; color: #ffffff; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #141414; padding: 40px; border-radius: 8px; border: 1px solid #333; text-align: center; }
        h1 { color: #e50914; font-size: 24px; margin-bottom: 20px; }
        p { font-size: 16px; line-height: 1.5; color: #cccccc; }
        .code-box { background-color: #222; color: #ffffff; font-size: 42px; font-weight: bold; padding: 20px; margin: 30px 0; border-radius: 4px; letter-spacing: 15px; border: 1px dashed #e50914; display: inline-block; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Recuperação de Senha</h1>
        <p>Olá,</p>
        <p>Recebemos uma solicitação para redefinir a senha da sua conta no <strong>{{ config('app.name') }}</strong>.</p>
        <p>Use o código abaixo para prosseguir com a redefinição no aplicativo:</p>
        
        <div class="code-box">{{ $code }}</div>
        
        <p>Este código expira em 15 minutos. Se você não solicitou isso, ignore este e-mail.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
        </div>
    </div>
</body>
</html>

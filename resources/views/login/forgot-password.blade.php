<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-950 text-white">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-neutral-900 p-8 rounded-lg w-full max-w-md space-y-6 shadow-xl border border-neutral-800">
            <div class="text-center">
                <h2 class="text-2xl font-bold">Esqueceu sua senha?</h2>
                <p class="text-xs text-neutral-500 mt-2">Informe seu e-mail e enviaremos um link para você criar uma nova senha.</p>
            </div>

            @if (session('status'))
                <div class="bg-green-600/20 text-green-400 p-3 rounded text-sm text-center border border-green-600/30">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-600/20 text-red-400 p-3 rounded text-sm border border-red-600/30">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Seu E-mail</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full p-2.5 bg-neutral-800 rounded mt-1 outline-none border border-neutral-700 focus:border-red-600 transition text-sm">
                </div>

                <button type="submit"
                    class="w-full bg-red-600 py-2.5 rounded hover:bg-red-700 transition font-bold uppercase text-xs tracking-widest shadow-lg shadow-red-900/20">
                    Enviar Link de Recuperação
                </button>
            </form>

            <div class="text-center pt-2">
                <a href="{{ route('login') }}" class="text-xs text-neutral-500 hover:text-white transition">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Voltar para o Login
                </a>
            </div>
        </div>
    </div>
</body>

</html>

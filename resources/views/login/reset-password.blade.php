<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-neutral-950 text-white">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-neutral-900 p-8 rounded-lg w-full max-w-md space-y-6 shadow-xl border border-neutral-800">
            <div class="text-center">
                <h2 class="text-2xl font-bold">Crie uma nova senha</h2>
                <p class="text-xs text-neutral-500 mt-2">Informe sua nova senha abaixo para restaurar o acesso à sua conta.</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-600/20 text-red-400 p-3 rounded text-sm border border-red-600/30">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Seu E-mail</label>
                    <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly
                        class="w-full p-2.5 bg-neutral-800 rounded mt-1 outline-none border border-neutral-700 text-neutral-500 text-sm cursor-not-allowed">
                </div>

                <div>
                    <label class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Nova Senha</label>
                    <input type="password" name="password" required autofocus
                        class="w-full p-2.5 bg-neutral-800 rounded mt-1 outline-none border border-neutral-700 focus:border-red-600 transition text-sm">
                </div>

                <div>
                    <label class="text-xs font-bold text-neutral-400 uppercase tracking-wider">Confirmar Nova Senha</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full p-2.5 bg-neutral-800 rounded mt-1 outline-none border border-neutral-700 focus:border-red-600 transition text-sm">
                </div>

                <button type="submit"
                    class="w-full bg-red-600 py-2.5 rounded hover:bg-red-700 transition font-bold uppercase text-xs tracking-widest shadow-lg shadow-red-900/20">
                    Redefinir Senha
                </button>
            </form>
        </div>
    </div>
</body>

</html>

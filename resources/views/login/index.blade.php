<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <title>Login</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-neutral-950 text-white">

    <div class="flex items-center justify-center min-h-screen">

        <form method="POST"
            action="{{ route('login.authenticate') }}"
            class="bg-neutral-900 p-8 rounded-lg w-full max-w-md space-y-5 shadow-xl">

            @csrf

            <h2 class="text-2xl font-bold text-center">
                Login
            </h2>

            {{-- erro --}}
            @if ($errors->any())
                <div class="bg-red-600/20 text-red-400 p-3 rounded text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <div>

                <label class="text-sm text-neutral-400">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    required
                    class="w-full p-2 bg-neutral-800 rounded mt-1 outline-none focus:ring-2 focus:ring-red-600">

            </div>

            <div>

                <label class="text-sm text-neutral-400">
                    Senha
                </label>

                <input
                    type="password"
                    name="password"
                    required
                    class="w-full p-2 bg-neutral-800 rounded mt-1 outline-none focus:ring-2 focus:ring-red-600">

            </div>

            <button
                class="w-full bg-red-600 py-2 rounded hover:bg-red-700 transition font-medium">

                Entrar

            </button>

        </form>

    </div>

</body>

</html>
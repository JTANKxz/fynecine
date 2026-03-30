@extends('layouts.admin')

@section('title', 'Nova Notificação')

@section('content')
<section class="max-w-4xl">
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('admin.notifications.index') }}" class="w-10 h-10 bg-neutral-900 border border-neutral-800 rounded-full flex items-center justify-center hover:bg-neutral-800 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-white">Disparar Notificação</h2>
            <p class="text-sm text-neutral-500">Crie e envie um novo alerta para o aplicativo.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-900/20 border border-red-600 text-red-400 px-4 py-3 rounded text-sm">
            <ul class="list-disc ml-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl p-6 md:p-8 shadow-2xl">
        <form action="{{ route('admin.notifications.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Título --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Título do Alerta</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Ex: Estreia de Hoje"
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">
                </div>

                {{-- Imagem Opcional --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">URL da Imagem (Opcional)</label>
                    <input type="url" name="image_url" value="{{ old('image_url') }}" placeholder="https://..."
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition text-xs font-mono">
                </div>
            </div>

            {{-- Conteúdo --}}
            <div class="space-y-2">
                <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Mensagem (Curta e Clara)</label>
                <textarea name="content" rows="3" required placeholder="Escreva aqui o corpo da notificação..."
                    class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">{{ old('content') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Tipo de Ação --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Ação ao Clicar</label>
                    <select name="action_type" required
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition cursor-pointer">
                        <option value="none">Nenhuma Ação (Apenas Texto)</option>
                        <option value="url">Abrir Link Externo (Browser)</option>
                        <option value="movie">Abrir Filme (Slug/ID)</option>
                        <option value="series">Abrir Série (Slug/ID)</option>
                        <option value="plans">Abrir Planos de Assinatura</option>
                    </select>
                </div>

                {{-- Valor da Ação --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Valor da Ação (Link/Slug)</label>
                    <input type="text" name="action_value" value="{{ old('action_value') }}" placeholder="Ex: avatar-2 ou https://..."
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition font-mono text-xs">
                    <p class="text-[9px] text-neutral-500">Obrigatório se a ação não for 'none' ou 'plans'.</p>
                </div>

                {{-- Data de Expiração --}}
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-neutral-400 uppercase tracking-widest text-[10px]">Data de Expiração (Opcional)</label>
                    <input type="datetime-local" name="expires_at" value="{{ old('expires_at') }}"
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition text-sm">
                    <p class="text-[9px] text-neutral-500">Após esta data, a notificação sumirá.</p>
                </div>
            </div>

            <div class="border-t border-neutral-800 pt-6 flex flex-col md:flex-row gap-8 items-center justify-between">
                {{-- Destinatário --}}
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="is_global" id="is_global" checked value="1"
                            class="w-5 h-5 accent-netflix rounded bg-black border-neutral-800">
                        <span class="text-white font-bold text-sm group-hover:text-netflix transition">ENVIAR PARA TODOS (GLOBAL)</span>
                    </label>

                    <div id="user_id_field" class="hidden flex items-center gap-2">
                        <span class="text-neutral-500 text-xs uppercase font-bold">USER ID:</span>
                        <input type="number" name="user_id" value="{{ old('user_id') }}" placeholder="Ex: 12"
                            class="w-24 bg-black border border-neutral-800 rounded-lg px-3 py-1.5 text-white focus:outline-none focus:border-netflix transition text-sm">
                    </div>
                </div>

                <button type="submit" class="w-full md:w-auto bg-netflix hover:bg-red-700 text-white font-black px-10 py-4 rounded-xl shadow-xl transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane mr-1"></i> DISPARAR AGORA
                </button>
            </div>
        </form>
    </div>
</section>

<script>
    const globalCheck = document.getElementById('is_global');
    const userField = document.getElementById('user_id_field');

    function updateFields() {
        if (globalCheck.checked) {
            userField.classList.add('hidden');
        } else {
            userField.classList.remove('hidden');
        }
    }

    globalCheck.addEventListener('change', updateFields);
    window.onload = updateFields;
</script>
@endsection

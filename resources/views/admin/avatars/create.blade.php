@extends('layouts.admin')

@section('title', 'Novo Avatar')

@section('content')
<section class="max-w-4xl">
    <div class="mb-6 text-center md:text-left">
        <h2 class="text-2xl font-bold">Novo Avatar</h2>
        <p class="text-neutral-400 text-sm">Adicione um novo avatar à sua biblioteca para os usuários escolherem em seus perfis.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-900/50 border border-red-600 text-red-200 px-4 py-3 rounded text-sm relative">
            <strong class="font-bold">Ops! Algo deu errado.</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-neutral-900 p-8 rounded-xl border border-neutral-800 shadow-xl">
        <form action="{{ route('admin.avatars.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Coluna Esquerda: Categoria e Opção URL -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-3 tracking-widest">Categoria do Avatar</label>
                        <select name="avatar_category_id" required
                                class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-3 focus:ring-2 focus:ring-netflix outline-none transition">
                            <option value="" disabled selected>Selecione uma categoria...</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('avatar_category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-[10px] text-neutral-500">Ex: Netflix, Star Wars, Pixar, etc.</p>
                    </div>

                    <div class="pt-4 border-t border-neutral-800">
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-3 tracking-widest">Opção A: URL da Imagem</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-500 group-focus-within:text-netflix transition">
                                <i class="fa-solid fa-link"></i>
                            </span>
                            <input type="url" name="image_url" value="{{ old('image_url') }}" placeholder="https://exemplo.com/avatar.png"
                                   class="w-full bg-neutral-800 border border-neutral-700 text-white rounded pl-10 pr-4 py-3 focus:ring-2 focus:ring-netflix outline-none transition">
                        </div>
                        <p class="mt-2 text-[10px] text-neutral-500">Use URLs de imagens externas se não quiser hospedar localmente.</p>
                    </div>
                </div>

                <!-- Coluna Direita: Upload de arquivo -->
                <div class="space-y-6">
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-3 tracking-widest">Opção B: Upload Local</label>
                    <div class="relative group h-full">
                        <label class="flex flex-col items-center justify-center w-full h-full min-h-[150px] border-2 border-dashed border-neutral-700 rounded-xl bg-neutral-800/50 hover:bg-neutral-800 hover:border-netflix cursor-pointer transition-all">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fa-solid fa-cloud-arrow-up text-3xl text-neutral-500 group-hover:text-netflix mb-3"></i>
                                <p class="mb-2 text-sm text-neutral-300">Selecione uma imagem</p>
                                <p class="text-xs text-neutral-500">JPG, PNG ou SVG (Recomendado: 512x512)</p>
                            </div>
                            <input type="file" name="image_file" class="hidden" accept="image/*" onchange="previewImage(this)">
                        </label>
                        <div id="preview-container" class="hidden absolute inset-0 bg-neutral-900 rounded-xl overflow-hidden pointer-events-none border-2 border-netflix">
                            <img id="preview-img" src="#" class="w-full h-full object-cover">
                            <button type="button" onclick="removePreview()" class="absolute top-2 right-2 bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg pointer-events-auto hover:bg-red-700">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-neutral-800">
                <a href="{{ route('admin.avatars.index') }}" class="px-8 py-3 rounded font-bold text-sm bg-neutral-800 hover:bg-neutral-700 transition">
                    Voltar para Lista
                </a>
                <button type="submit" class="bg-netflix px-10 py-3 rounded font-bold text-sm shadow-lg hover:bg-red-700 hover:scale-105 active:scale-95 transition transform">
                    <i class="fa-solid fa-floppy-disk mr-2"></i>Salvar Avatar
                </button>
            </div>
        </form>
    </div>
</section>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('preview-container').classList.remove('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removePreview() {
        document.getElementById('preview-container').classList.add('hidden');
        const input = document.querySelector('input[type="file"]');
        input.value = '';
    }
</script>
@endsection

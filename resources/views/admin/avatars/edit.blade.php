@extends('layouts.admin')

@section('title', 'Editar Avatar')

@section('content')
<section class="max-w-4xl">
    <div class="mb-6">
        <h2 class="text-2xl font-bold">Editar Avatar</h2>
        <p class="text-neutral-400 text-sm">Atualize a categoria ou a imagem deste avatar.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-900/50 border border-red-600 text-red-200 px-4 py-2 rounded text-sm relative">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-neutral-900 p-8 rounded-xl border border-neutral-800 shadow-xl">
        <form action="{{ route('admin.avatars.update', $avatar->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')
            
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Coluna Esquerda: Categoria e Opção URL -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-3 tracking-widest">Categoria do Avatar</label>
                        <select name="avatar_category_id" required
                                class="w-full bg-neutral-800 border border-neutral-700 text-white rounded px-4 py-3 focus:ring-2 focus:ring-netflix outline-none transition">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('avatar_category_id', $avatar->avatar_category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4 border-t border-neutral-800">
                        <label class="block text-xs font-bold text-neutral-500 uppercase mb-3 tracking-widest text-netflix">Substituir por URL</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-500 group-focus-within:text-netflix transition">
                                <i class="fa-solid fa-link"></i>
                            </span>
                            <input type="url" name="image_url" value="{{ old('image_url', filter_var($avatar->image, FILTER_VALIDATE_URL) ? $avatar->image : '') }}"
                                   class="w-full bg-neutral-800 border border-neutral-700 text-white rounded pl-10 pr-4 py-3 focus:ring-2 focus:ring-netflix outline-none transition">
                        </div>
                    </div>
                </div>

                <!-- Coluna Direita: Imagem Atual e Novo Upload -->
                <div class="space-y-6">
                    <label class="block text-xs font-bold text-neutral-500 uppercase mb-3 tracking-widest">Imagem Atual e Upload</label>
                    <div class="relative h-[250px] group rounded-xl overflow-hidden border-2 border-dashed border-neutral-700 bg-neutral-800/50">
                        
                        <div id="preview-placeholder" class="flex flex-col items-center justify-center p-6 h-full text-center">
                            <img src="{{ $avatar->image_url }}" alt="Atual" class="w-32 h-32 rounded-lg object-cover mb-4 border-2 border-netflix shadow-lg">
                            <p class="text-xs text-neutral-400">Clique para substituir a imagem</p>
                            <p class="text-[10px] text-neutral-600 mt-1">JPG, PNG ou SVG</p>
                        </div>

                        <label class="absolute inset-0 cursor-pointer opacity-0 hover:opacity-10 dark:hover:opacity-20 transition-opacity">
                            <input type="file" name="image_file" class="hidden" accept="image/*" onchange="previewImage(this)">
                        </label>

                        <!-- Preview de Nova Imagem (escondido inicialmente) -->
                        <div id="preview-container" class="hidden absolute inset-0 bg-neutral-900 rounded-xl overflow-hidden pointer-events-none border-2 border-green-500">
                            <img id="preview-img" src="#" class="w-full h-full object-cover">
                            <span class="absolute top-2 left-2 bg-green-500 text-white px-2 py-0.5 rounded-md text-[10px] font-bold">NOVA IMAGEM SELECIONADA</span>
                            <button type="button" onclick="removePreview()" class="absolute top-2 right-2 bg-red-600 text-white w-8 h-8 rounded-full flex items-center justify-center shadow-lg pointer-events-auto hover:bg-red-700">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-neutral-800">
                <a href="{{ route('admin.avatars.index') }}" class="px-8 py-3 rounded font-bold text-sm bg-neutral-800 hover:bg-neutral-700 transition">
                    Ver Todos
                </a>
                <button type="submit" class="bg-netflix px-10 py-3 rounded font-bold text-sm shadow-lg hover:bg-red-700 hover:scale-105 active:scale-95 transition transform">
                    <i class="fa-solid fa-cloud-arrow-up mr-2 text-white"></i>Salvar Alterações
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
                document.getElementById('preview-placeholder').classList.add('opacity-10');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removePreview() {
        document.getElementById('preview-container').classList.add('hidden');
        document.getElementById('preview-placeholder').classList.remove('opacity-10');
        const input = document.querySelector('input[type="file"]');
        input.value = '';
    }
</script>
@endsection

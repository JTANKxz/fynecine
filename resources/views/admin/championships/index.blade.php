@extends('layouts.admin')

@section('title', 'Gerenciar Campeonatos')

@section('content')
<section class="max-w-5xl">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-white">Campeonatos</h2>
            <p class="text-sm text-neutral-500">Gerencie as competições dos eventos ao vivo.</p>
        </div>
        <button onclick="openModal('create')" class="bg-netflix hover:bg-neutral-800 text-white font-bold px-6 py-3 rounded-xl shadow-lg transition flex items-center gap-2 w-fit">
            <i class="fa-solid fa-plus"></i> NOVO CAMPEONATO
        </button>
    </div>

    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl overflow-hidden shadow-2xl">
        <div class="table-container">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-black text-neutral-400 uppercase tracking-widest text-[10px] font-bold">
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Nome</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800">
                    @forelse($championships as $cp)
                        <tr class="hover:bg-neutral-800/50 transition">
                            <td class="px-6 py-4 text-neutral-500 font-mono">#{{ $cp->id }}</td>
                            <td class="px-6 py-4 font-bold text-white">{{ $cp->name }}</td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="openModal('edit', {{ $cp->id }}, '{{ addslashes($cp->name) }}')" class="text-blue-400 hover:text-blue-300 transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <form action="{{ route('admin.championships.destroy', $cp->id) }}" method="POST" class="inline-block swal-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-400 transition swal-delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-neutral-500">Nenhum campeonato cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($championships->hasPages())
            <div class="p-4 border-t border-neutral-800">
                {{ $championships->links() }}
            </div>
        @endif
    </div>
</section>

{{-- Modal --}}
<div id="champModal" class="fixed inset-0 bg-black/80 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-neutral-900 border border-neutral-800 rounded-2xl w-full max-w-md overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-neutral-800 flex justify-between items-center bg-black/20">
            <h3 id="modalTitle" class="text-lg font-bold text-white">Novo Campeonato</h3>
            <button onclick="closeModal()" class="text-neutral-500 hover:text-white transition">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <form id="modalForm" action="{{ route('admin.championships.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            
            <div class="space-y-2">
                <label class="block text-xs font-bold text-neutral-400 uppercase">Nome do Campeonato</label>
                <input type="text" name="name" id="champName" required placeholder="Ex: Premier League"
                    class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-netflix transition">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="flex-1 bg-neutral-800 hover:bg-neutral-700 text-white font-bold py-3 rounded-xl transition">
                    CANCELAR
                </button>
                <button type="submit" class="flex-1 bg-netflix hover:bg-red-700 text-white font-bold py-3 rounded-xl transition">
                    SALVAR
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openModal(mode, id = null, name = '') {
        const modal = document.getElementById('champModal');
        const form = document.getElementById('modalForm');
        const title = document.getElementById('modalTitle');
        const nameInput = document.getElementById('champName');
        const methodInput = document.getElementById('formMethod');

        if (mode === 'edit') {
            title.textContent = 'Editar Campeonato';
            form.action = `/admin/championships/${id}`;
            methodInput.value = 'PATCH';
            nameInput.value = name;
        } else {
            title.textContent = 'Novo Campeonato';
            form.action = '{{ route("admin.championships.store") }}';
            methodInput.value = 'POST';
            nameInput.value = '';
        }

        modal.classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('champModal').classList.add('hidden');
    }

    // Close on escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });

    // SweetAlert for delete
    const deleteButtons = document.querySelectorAll('.swal-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            Swal.fire({
                title: 'Tem certeza?',
                text: "Isso removerá o campeonato!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#8B2FFF',
                cancelButtonColor: '#1a1a1a',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar',
                background: '#0f0f0f',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
@endsection

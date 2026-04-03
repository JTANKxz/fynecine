@extends('layouts.admin')

@section('title', 'Anotações do Administrador')

@section('content')
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Anotações</h1>
            <p class="text-neutral-400 mt-1">Gerencie tarefas, ideias e lembretes para a equipe.</p>
        </div>
        <button onclick="openNoteModal()"
            class="bg-netflix hover:bg-netflix/90 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-netflix/20 transition-all active:scale-95 flex items-center gap-2 w-fit">
            <i class="fa-solid fa-plus"></i>
            Nova Anotação
        </button>
    </div>

    <!-- Filtros e Busca (Opcional, mas bom para UX) -->
    <div class="mb-8 flex items-center gap-3 overflow-x-auto pb-2">
        <button class="filter-btn active px-4 py-2 rounded-full bg-netflix text-white text-sm font-medium whitespace-nowrap transition-all" data-filter="all">Todas</button>
        <button class="filter-btn px-4 py-2 rounded-full bg-neutral-800 text-neutral-400 text-sm font-medium whitespace-nowrap hover:bg-neutral-700 transition-all font-bold" data-filter="pinned">Fixadas</button>
        <button class="filter-btn px-4 py-2 rounded-full bg-neutral-800 text-neutral-400 text-sm font-medium whitespace-nowrap hover:bg-neutral-700 transition-all" data-filter="purple">Roxo</button>
        <button class="filter-btn px-4 py-2 rounded-full bg-neutral-800 text-neutral-400 text-sm font-medium whitespace-nowrap hover:bg-neutral-700 transition-all" data-filter="emerald">Verde</button>
        <button class="filter-btn px-4 py-2 rounded-full bg-neutral-800 text-neutral-400 text-sm font-medium whitespace-nowrap hover:bg-neutral-700 transition-all" data-filter="amber">Amarelo</button>
    </div>

    <!-- Grid de Notas -->
    <div id="notes-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($notes as $note)
            @include('admin.notes.partials.note_card', ['note' => $note])
        @empty
            <div id="no-notes-message" class="col-span-full flex flex-col items-center justify-center py-20 text-neutral-500 border-2 border-dashed border-neutral-800 rounded-3xl">
                <i class="fa-solid fa-note-sticky text-6xl mb-4 opacity-20"></i>
                <p class="text-lg font-medium">Nenhuma anotação encontrada.</p>
                <p class="text-sm">Clique em "Nova Anotação" para começar.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal de Nota (Tailwind + JS) -->
    <div id="note-modal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-[60] flex items-center justify-center p-4 hidden opacity-0 transition-opacity duration-300">
        <div class="bg-neutral-900 border border-neutral-800 w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden transform scale-95 transition-transform duration-300" id="modal-content">
            <div class="p-6 border-b border-neutral-800 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white" id="modal-title">Nova Anotação</h3>
                <button onclick="closeNoteModal()" class="text-neutral-500 hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            
            <form id="note-form" onsubmit="saveNote(event)" class="p-6 space-y-5">
                @csrf
                <input type="hidden" id="note-id" name="id">
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-neutral-400">Título (Opcional)</label>
                    <input type="text" id="form-title" name="title" placeholder="Ex: Tarefas de amanhã"
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:border-netflix focus:ring-1 focus:ring-netflix outline-none transition-all placeholder:text-neutral-700">
                </div>

                <div class="space-y-4 p-1 bg-black/40 rounded-xl flex">
                    <label class="flex-1 cursor-pointer group">
                        <input type="radio" name="type" value="note" checked onchange="toggleFormType(this.value)" class="hidden peer">
                        <div class="py-2 text-center rounded-lg text-sm font-bold transition-all peer-checked:bg-netflix peer-checked:text-white text-neutral-500 hover:text-neutral-300">
                            <i class="fa-solid fa-file-lines mr-2"></i>Texto
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer group">
                        <input type="radio" name="type" value="tasks" onchange="toggleFormType(this.value)" class="hidden peer">
                        <div class="py-2 text-center rounded-lg text-sm font-bold transition-all peer-checked:bg-netflix peer-checked:text-white text-neutral-500 hover:text-neutral-300">
                            <i class="fa-solid fa-list-check mr-2"></i>Tarefas
                        </div>
                    </label>
                </div>

                <div id="type-note-area" class="space-y-2">
                    <label class="text-sm font-medium text-neutral-400">Conteúdo</label>
                    <textarea id="form-content" name="content" rows="4" placeholder="O que você está pensando?"
                        class="w-full bg-black border border-neutral-800 rounded-xl px-4 py-3 text-white focus:border-netflix focus:ring-1 focus:ring-netflix outline-none transition-all placeholder:text-neutral-700 resize-none"></textarea>
                </div>

                <div id="type-tasks-area" class="space-y-2 hidden">
                    <label class="text-sm font-medium text-neutral-400 flex justify-between">
                        Lista de Tarefas
                        <button type="button" onclick="addTaskRow()" class="text-netflix hover:text-white transition-colors text-xs font-bold uppercase tracking-wider">
                            <i class="fa-solid fa-plus mr-1"></i>Adicionar
                        </button>
                    </label>
                    <div id="tasks-container" class="space-y-2 max-h-48 overflow-y-auto pr-1 custom-scrollbar">
                        <!-- Tasks will be added here -->
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-neutral-400">Cor da Categoria</label>
                    <div class="flex flex-wrap gap-3">
                        <label class="cursor-pointer group">
                            <input type="radio" name="color" value="purple" checked class="hidden peer">
                            <div class="w-10 h-10 rounded-full bg-purple-600 border-2 border-transparent peer-checked:border-white peer-checked:scale-110 transition-all group-hover:scale-105 shadow-lg shadow-purple-900/40"></div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="color" value="emerald" class="hidden peer">
                            <div class="w-10 h-10 rounded-full bg-emerald-600 border-2 border-transparent peer-checked:border-white peer-checked:scale-110 transition-all group-hover:scale-105 shadow-lg shadow-emerald-900/40"></div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="color" value="amber" class="hidden peer">
                            <div class="w-10 h-10 rounded-full bg-amber-600 border-2 border-transparent peer-checked:border-white peer-checked:scale-110 transition-all group-hover:scale-105 shadow-lg shadow-amber-900/40"></div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="color" value="rose" class="hidden peer">
                            <div class="w-10 h-10 rounded-full bg-rose-600 border-2 border-transparent peer-checked:border-white peer-checked:scale-110 transition-all group-hover:scale-105 shadow-lg shadow-rose-900/40"></div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="color" value="blue" class="hidden peer">
                            <div class="w-10 h-10 rounded-full bg-blue-600 border-2 border-transparent peer-checked:border-white peer-checked:scale-110 transition-all group-hover:scale-105 shadow-lg shadow-blue-900/40"></div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="color" value="neutral" class="hidden peer">
                            <div class="w-10 h-10 rounded-full bg-neutral-700 border-2 border-transparent peer-checked:border-white peer-checked:scale-110 transition-all group-hover:scale-105 shadow-lg shadow-neutral-900/40"></div>
                        </label>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" id="form-pinned" name="is_pinned" class="w-5 h-5 accent-netflix rounded cursor-pointer">
                    <label for="form-pinned" class="text-sm font-medium text-neutral-300 cursor-pointer">Fixar esta nota no topo</label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="button" onclick="closeNoteModal()"
                        class="flex-1 px-6 py-3 rounded-xl border border-neutral-800 text-neutral-400 font-bold hover:bg-neutral-800 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" id="submit-btn"
                        class="flex-1 px-6 py-3 rounded-xl bg-netflix text-white font-bold hover:bg-netflix/90 transition-all shadow-lg shadow-netflix/20">
                        Salvar Nota
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('note-modal');
    const modalContent = document.getElementById('modal-content');
    const noteForm = document.getElementById('note-form');
    
    function toggleFormType(type) {
        if (type === 'note') {
            document.getElementById('type-note-area').classList.remove('hidden');
            document.getElementById('type-tasks-area').classList.add('hidden');
            document.getElementById('form-content').required = true;
        } else {
            document.getElementById('type-note-area').classList.add('hidden');
            document.getElementById('type-tasks-area').classList.remove('hidden');
            document.getElementById('form-content').required = false;
            
            // Add initial task if empty
            if (document.getElementById('tasks-container').children.length === 0) {
                addTaskRow();
            }
        }
    }

    function addTaskRow(text = '', done = false) {
        const container = document.getElementById('tasks-container');
        const row = document.createElement('div');
        row.className = 'task-row group flex items-center gap-3 bg-black/60 p-2 rounded-xl border border-neutral-800 focus-within:border-netflix transition-all';
        row.innerHTML = `
            <input type="checkbox" name="tasks[][done]" ${done ? 'checked' : ''} value="1" 
                class="w-5 h-5 rounded accent-emerald-500 bg-neutral-900 border-neutral-700">
            <input type="text" name="tasks[][text]" value="${text}" placeholder="Nova tarefa..."
                class="flex-1 bg-transparent border-none outline-none text-sm text-white placeholder:text-neutral-700">
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 opacity-0 group-hover:opacity-100 transition-opacity p-1">
                <i class="fa-solid fa-trash-can text-sm"></i>
            </button>
        `;
        container.appendChild(row);
        row.querySelector('input[type="text"]').focus();
    }

    function openNoteModal(editData = null) {
        if (editData) {
            document.getElementById('modal-title').innerText = 'Editar Anotação';
            document.getElementById('note-id').value = editData.id;
            document.getElementById('form-title').value = editData.title || '';
            document.getElementById('form-content').value = editData.content || '';
            document.getElementById('form-pinned').checked = editData.is_pinned;
            
            // Set type
            const typeValue = editData.type || 'note';
            const typeRadio = document.querySelector(`input[name="type"][value="${typeValue}"]`);
            if (typeRadio) {
                typeRadio.checked = true;
                toggleFormType(typeValue);
            }

            // Set tasks
            const tasksContainer = document.getElementById('tasks-container');
            tasksContainer.innerHTML = '';
            if (editData.tasks && editData.tasks.length > 0) {
                editData.tasks.forEach(task => addTaskRow(task.text, task.done));
            }
            
            // Set color radio
            const colorRadios = document.querySelectorAll('input[name="color"]');
            colorRadios.forEach(radio => {
                if (radio.value === editData.color) radio.checked = true;
            });
            
            document.getElementById('submit-btn').innerText = 'Atualizar Nota';
        } else {
            document.getElementById('modal-title').innerText = 'Nova Anotação';
            noteForm.reset();
            document.getElementById('note-id').value = '';
            document.getElementById('tasks-container').innerHTML = '';
            document.getElementById('submit-btn').innerText = 'Salvar Nota';
            document.querySelector('input[name="color"][value="purple"]').checked = true;
            document.querySelector('input[name="type"][value="note"]').checked = true;
            toggleFormType('note');
        }
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }, 10);
    }

    function closeNoteModal() {
        modal.classList.remove('opacity-100');
        modalContent.classList.remove('scale-100');
        modalContent.classList.add('scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    async function saveNote(e) {
        e.preventDefault();
        
        // Prepare tasks
        const type = document.querySelector('input[name="type"]:checked').value;
        const formData = new FormData(noteForm);
        
        if (type === 'tasks') {
            // Limpar tasks antigas e remontar corretamente para o backend
            formData.delete('tasks[][text]');
            formData.delete('tasks[][done]');
            
            const taskRows = document.querySelectorAll('.task-row');
            taskRows.forEach((row, index) => {
                const text = row.querySelector('input[type="text"]').value;
                const done = row.querySelector('input[type="checkbox"]').checked;
                if (text.trim()) {
                    formData.append(`tasks[${index}][text]`, text);
                    formData.append(`tasks[${index}][done]`, done ? 1 : 0);
                }
            });
        }

        const id = formData.get('id');
        const url = id ? `{{ url('dashzin/notes') }}/${id}` : `{{ route('admin.notes.store') }}`;
        const method = id ? 'PUT' : 'POST';
        
        // Convert FormData to object for PUT requests if needed, but fetch usually handles POST decoration
        let body = formData;
        if (id) {
            // Laravel needs _method for PUT in FormData, or JSON
            formData.append('_method', 'PUT');
        }

        try {
            const response = await fetch(url, {
                method: 'POST', // Use POST with _method spoofing for Laravel
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                closeNoteModal();
                Swal.fire({
                    icon: 'success',
                    title: data.message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    background: '#1a1a1a',
                    color: '#fff'
                });
                
                // Real-time update (simplistic: reload for now or append if new)
                // For a more premium experience, we Refresh the grid or specific card
                setTimeout(() => window.location.reload(), 1000); 
            } else {
                throw new Error(data.message || 'Erro ao salvar nota');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Ops!',
                text: error.message,
                background: '#1a1a1a',
                color: '#fff'
            });
        }
    }

    async function deleteNote(id) {
        const result = await Swal.fire({
            title: 'Excluir Nota?',
            text: "Esta ação não pode ser desfeita!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#8B2FFF',
            cancelButtonColor: '#1a1a1a',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar',
            background: '#1a1a1a',
            color: '#fff'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`{{ url('dashzin/notes') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    const card = document.getElementById(`note-${id}`);
                    card.classList.add('scale-90', 'opacity-0');
                    setTimeout(() => card.remove(), 300);
                }
            } catch (error) {
                console.error(error);
            }
        }
    }

    async function togglePin(id) {
        try {
            const response = await fetch(`{{ url('dashzin/notes') }}/${id}/pin`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                window.location.reload(); // Reload to reorder properly
            }
        } catch (error) {
            console.error(error);
        }
    }

    async function toggleTask(noteId, index) {
        const check = document.getElementById(`task-${noteId}-${index}-check`);
        const text = document.getElementById(`task-${noteId}-${index}-text`);
        
        // Optimistic UI
        const isDone = text.classList.contains('line-through');
        
        if (isDone) {
            check.classList.remove('bg-emerald-500', 'border-emerald-500');
            check.classList.add('bg-white/5');
            check.innerHTML = '';
            text.classList.remove('text-white/30', 'line-through');
            text.classList.add('text-white/80');
        } else {
            check.classList.remove('bg-white/5');
            check.classList.add('bg-emerald-500', 'border-emerald-500');
            check.innerHTML = '<i class="fa-solid fa-check text-[10px] text-white"></i>';
            text.classList.remove('text-white/80');
            text.classList.add('text-white/30', 'line-through');
        }

        try {
            const response = await fetch(`{{ url('dashzin/notes') }}/${noteId}/task`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ index })
            });

            const data = await response.json();
            if (!data.success) throw new Error();
        } catch (error) {
            // Fallback en caso de error? Podríamos recargar, pero es más suave así
            console.error('Erro ao alternar tarefa');
        }
    }

    function editNote(id) {
        const card = document.getElementById(`note-${id}`);
        const type = card.querySelector('.task-list') ? 'tasks' : 'note';
        const title = card.querySelector('.note-title')?.innerText || '';
        const isPinned = card.querySelector('.absolute.-top-2') !== null;
        
        let content = '';
        let tasks = [];

        if (type === 'note') {
            content = card.querySelector('.note-content').innerText;
        } else {
            const taskRows = card.querySelectorAll('.task-list > div');
            taskRows.forEach(row => {
                const taskText = row.querySelector('span').innerText;
                const taskDone = row.querySelector('.bg-emerald-500') !== null;
                tasks.push({ text: taskText, done: taskDone });
            });
        }
        
        // Find color
        let color = 'purple';
        const innerCard = card.querySelector('.rounded-xl');
        if (innerCard.classList.contains('bg-emerald-600/10')) color = 'emerald';
        else if (innerCard.classList.contains('bg-amber-600/10')) color = 'amber';
        else if (innerCard.classList.contains('bg-rose-600/10')) color = 'rose';
        else if (innerCard.classList.contains('bg-blue-600/10')) color = 'blue';
        else if (innerCard.classList.contains('bg-neutral-900')) color = 'neutral';

        openNoteModal({ id, title, content, type, tasks, is_pinned: isPinned, color });
    }

    // Filtros
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('bg-netflix', 'text-white');
                b.classList.add('bg-neutral-800', 'text-neutral-400');
            });
            btn.classList.add('bg-netflix', 'text-white');
            btn.classList.remove('bg-neutral-800', 'text-neutral-400');

            const filter = btn.getAttribute('data-filter');
            const cards = document.querySelectorAll('.note-card');
            
            cards.forEach(card => {
                if (filter === 'all') {
                    card.classList.remove('hidden');
                } else if (filter === 'pinned') {
                    card.classList.toggle('hidden', !card.querySelector('.absolute.-top-2'));
                } else {
                    const innerCard = card.querySelector('.rounded-xl');
                    const colorClass = `bg-${filter}-600/10`;
                    card.classList.toggle('hidden', !innerCard.classList.contains(colorClass));
                }
            });
        });
    });
</script>
@endpush

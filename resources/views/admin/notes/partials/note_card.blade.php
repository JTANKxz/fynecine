<div class="note-card transition-all duration-300 transform hover:-translate-y-1 relative group" data-id="{{ $note->id }}" id="note-{{ $note->id }}">
    <div class="rounded-xl p-5 h-full flex flex-col border border-white/5 shadow-lg
        @if($note->color == 'purple') bg-purple-600/10 border-purple-500/20 @elseif($note->color == 'emerald') bg-emerald-600/10 border-emerald-500/20 @elseif($note->color == 'amber') bg-amber-600/10 border-amber-500/20 @elseif($note->color == 'rose') bg-rose-600/10 border-rose-500/20 @elseif($note->color == 'blue') bg-blue-600/10 border-blue-500/20 @else bg-neutral-900 border-neutral-700 @endif">
        
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1 mr-2">
                @if($note->title)
                    <h3 class="font-bold text-white text-lg leading-tight mb-1 note-title">{{ $note->title }}</h3>
                @endif
                <span class="text-[10px] uppercase tracking-wider text-white/40 font-medium">
                    {{ $note->user->name ?? 'Admin' }} • {{ $note->created_at->diffForHumans() }}
                </span>
            </div>
            
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button onclick="togglePin({{ $note->id }})" class="p-1.5 hover:bg-white/10 rounded-lg transition-colors pin-btn {{ $note->is_pinned ? 'text-amber-400' : 'text-white/40' }}" title="Fixar nota">
                    <i class="fa-solid fa-thumbtack"></i>
                </button>
                <button onclick="editNote({{ $note->id }})" class="p-1.5 hover:bg-white/10 rounded-lg text-white/40 hover:text-white transition-colors" title="Editar">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button onclick="deleteNote({{ $note->id }})" class="p-1.5 hover:bg-white/10 rounded-lg text-white/40 hover:text-rose-500 transition-colors" title="Excluir">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>

        <div class="text-white/80 text-sm whitespace-pre-wrap flex-1 note-content">{{ $note->content }}</div>

        @if($note->is_pinned)
            <div class="absolute -top-2 -right-2 bg-amber-500 text-black text-[10px] font-bold px-2 py-0.5 rounded-full shadow-lg ring-2 ring-black">
                FIXADA
            </div>
        @endif
    </div>
</div>

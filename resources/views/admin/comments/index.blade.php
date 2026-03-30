@extends('layouts.admin')

@section('title', 'Moderação de Comentários')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-white">Comentários</h1>
            <p class="mt-2 text-sm text-neutral-400">Modere comentários feitos em Filmes ou Séries pelos usuários.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mt-4 bg-green-900 border border-green-600 text-green-100 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-neutral-800">
                        <thead class="bg-neutral-800">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-white sm:pl-6">Usuário</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Onde?</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Comentário</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Data</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800 bg-neutral-900">
                            @forelse ($comments as $comment)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ $comment->profile->avatar_url ?? 'https://ui-avatars.com/api/?name='.$comment->profile->name }}" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-medium text-white">{{ $comment->profile->name }}</div>
                                                <div class="text-neutral-500 text-xs">{{ $comment->profile->user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-neutral-300">
                                        {{ $comment->commentable_type === 'App\\Models\\Movie' ? 'Filme' : 'Série' }}: <br>
                                        <span class="text-white font-semibold">{{ $comment->commentable->title ?? $comment->commentable->name ?? 'Excluído' }}</span>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-neutral-300 max-w-xs truncate" title="{{ $comment->body }}">
                                        {{ Str::limit($comment->body, 50) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-neutral-300">
                                        @if($comment->approved)
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Aprovado</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Oculto</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-neutral-400">
                                        {{ $comment->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 flex justify-end gap-2">
                                        
                                        <form action="{{ route('admin.comments.toggle', $comment) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="text-blue-500 hover:text-blue-400">
                                                {{ $comment->approved ? 'Ocultar' : 'Aprovar' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.comments.destroy', $comment) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-400 ml-3">
                                                Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-neutral-400">Nenhum comentário encontrado.</td>
                                </tr>
                            @endForelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $comments->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

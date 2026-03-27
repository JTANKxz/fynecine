@extends('layouts.admin')

@section('title', 'Pedidos de Conteúdo')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-xl font-semibold text-white">Pedidos da Comunidade</h1>
            <p class="mt-2 text-sm text-neutral-400">Usuários solicitam filmes e séries pelo App. Importe diretamente daqui.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="mt-4 bg-green-900 border border-green-600 text-green-100 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mt-4 bg-red-900 border border-red-600 text-red-100 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
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
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">TMDB / Título</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Data Pedido</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-white">Status</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">
                                    <span class="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-800 bg-neutral-900">
                            @forelse ($requests as $req)
                                <tr>
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <img class="h-10 w-10 rounded-full" src="{{ $req->user->avatar ?? 'https://ui-avatars.com/api/?name='.$req->user->name }}" alt="">
                                            </div>
                                            <div class="ml-4">
                                                <div class="font-medium text-white">{{ collect(explode(' ', $req->user->name))->take(2)->implode(' ') }} <span class="bg-neutral-800 text-xs px-2 py-0.5 rounded ml-2">{{ strtoupper($req->user->plan_type) }}</span></div>
                                                <div class="text-neutral-500">{{ $req->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-neutral-300">
                                        <div class="text-white font-semibold flex items-center gap-2">
                                            @if($req->type === 'movie')
                                            <span class="bg-blue-900 text-blue-200 text-xs px-1.5 py-0.5 rounded">FILME</span>
                                            @else
                                            <span class="bg-purple-900 text-purple-200 text-xs px-1.5 py-0.5 rounded">TV</span>
                                            @endif
                                            {{ $req->title }} ({{ $req->year ?? 'Ano N/D' }})
                                        </div>
                                        <div class="text-xs text-neutral-500 mt-1">ID TMDB: {{ $req->tmdb_id }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-neutral-400">
                                        {{ $req->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-neutral-300">
                                        @if($req->status === 'approved')
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">Aprovado</span>
                                        @elseif($req->status === 'rejected')
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20">Rejeitado</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">Pendente</span>
                                        @endif
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 flex justify-end gap-2 items-center">
                                        
                                        @if($req->status === 'pending')
                                        {{-- Botão mágico Importar --}}
                                        <form action="{{ route('admin.requests.autoimport', $req) }}" method="POST" class="inline" onsubmit="return confirm('Isso fará o download de todos os episódios/links originais do TMDB. Continuar?')">
                                            @csrf
                                            <button type="submit" class="bg-netflix hover:bg-red-700 text-white px-3 py-1.5 rounded mr-2 text-xs font-bold transition">
                                                <i class="fa-solid fa-download mr-1"></i> Auto-Importar
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.requests.update', $req) }}" method="POST" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="text-neutral-400 hover:text-white mr-3 text-xs">
                                                Rejeitar
                                            </button>
                                        </form>
                                        @endif

                                        <form action="{{ route('admin.requests.destroy', $req) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja apagar o registro?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-400 ml-1 text-xs">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-neutral-400">Nenhum pedido de filme/série pendente.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'EPG dos Canais')
@section('content')
<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Programa??o dos Canais (EPG)</h2>
            <p class="text-sm text-neutral-500">Associe cada canal ao guia XMLTV correspondente e atualize a grade.</p>
        </div>
        <a href="{{ route('admin.channels.index') }}" class="rounded bg-neutral-800 px-4 py-2 text-sm">Voltar</a>
    </div>

    @if(session('success'))
        <div class="rounded border border-green-700 bg-green-900/30 p-3 text-sm text-green-200">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded border border-red-700 bg-red-900/30 p-3 text-sm text-red-200">{{ session('error') }}</div>
    @endif

    <div class="rounded-xl border border-neutral-800 bg-neutral-900 p-5">
        <form method="POST" action="{{ route('admin.channels.epg.source') }}" class="grid gap-3 md:grid-cols-[180px_1fr_auto]">
            @csrf
            <input name="name" value="{{ old('name', $source?->name) }}" class="rounded bg-neutral-800 p-2" placeholder="Nome da fonte">
            <input id="epg-source-url" name="url" value="{{ old('url', $source?->url) }}" class="rounded bg-neutral-800 p-2" placeholder="URL XMLTV">
            <button class="rounded bg-neutral-700 px-4 py-2 font-bold">Salvar fonte</button>
        </form>

        <div class="mt-4 rounded-lg border border-neutral-800 bg-neutral-950/50 p-3 text-xs text-neutral-400">
            <p class="mb-2 font-semibold text-neutral-200">Fontes gratuitas recomendadas</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" data-epg-name="IPTV-EPG Brasil" data-epg-url="https://iptv-epg.org/files/epg-br.xml" class="rounded bg-neutral-800 px-3 py-1.5 hover:bg-neutral-700">IPTV-EPG Brasil ? recomendada</button>
                <button type="button" data-epg-name="EPGShare Brasil 1" data-epg-url="https://epgshare01.online/epgshare01/epg_ripper_BR1.xml.gz" class="rounded bg-neutral-800 px-3 py-1.5 hover:bg-neutral-700">EPGShare BR1</button>
                <button type="button" data-epg-name="EPGShare Brasil 2" data-epg-url="https://epgshare01.online/epgshare01/epg_ripper_BR2.xml.gz" class="rounded bg-neutral-800 px-3 py-1.5 hover:bg-neutral-700">EPGShare BR2</button>
            </div>
            <p class="mt-2">Trocar de fonte remove os v?nculos anteriores para evitar que um canal fique associado a um identificador antigo.</p>
        </div>

        <form method="POST" action="{{ route('admin.channels.epg.sync') }}" class="mt-4 flex flex-wrap items-center gap-3">
            @csrf
            <button class="rounded bg-purple-600 px-4 py-2 font-bold hover:bg-purple-500">Atualizar programa??o agora</button>
            <span class="text-xs text-neutral-500">?ltima atualiza??o: {{ $source?->last_synced_at?->format('d/m/Y H:i') ?? 'nunca' }}</span>
        </form>
    </div>

    <div class="overflow-x-auto rounded-xl border border-neutral-800 bg-neutral-900">
        <table class="w-full text-left text-sm">
            <thead class="bg-neutral-800 text-neutral-400"><tr><th class="p-3">Canal</th><th class="p-3">Canal no EPG XMLTV</th><th class="p-3"></th></tr></thead>
            <tbody>
                @foreach($channels as $channel)
                    <tr class="border-t border-neutral-800">
                        <td class="p-3 font-bold">{{ $channel->name }}</td>
                        <td class="p-3">
                            <form id="epg-{{ $channel->id }}" method="POST" action="{{ route('admin.channels.epg.mapping', $channel) }}">
                                @csrf @method('PUT')
                                <select name="epg_channel_id" class="w-full rounded bg-neutral-800 p-2">
                                    <option value="">Sem programa??o</option>
                                    @foreach($epgChannels as $epg)
                                        <option value="{{ $epg->id }}" @selected($channel->epgMapping?->epg_channel_id === $epg->id)>{{ $epg->name }} ? {{ $epg->xmltv_id }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="p-3"><button form="epg-{{ $channel->id }}" class="rounded bg-neutral-700 px-3 py-2">Salvar</button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $channels->links() }}</div>
    </div>
</section>

<script>
    document.querySelectorAll('[data-epg-url]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelector('#epg-source-url').value = button.dataset.epgUrl;
            document.querySelector('input[name="name"]').value = button.dataset.epgName;
        });
    });
</script>
@endsection

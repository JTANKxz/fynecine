<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EpgChannel;
use App\Models\EpgProgram;
use App\Models\EpgSource;
use App\Models\TvChannel;
use App\Models\TvChannelEpg;
use App\Services\EpgSyncService;
use Illuminate\Http\Request;

class EpgController extends Controller
{
    public function index()
    {
        $source = EpgSource::first();
        $channels = TvChannel::with('epgMapping.epgChannel')->orderBy('name')->paginate(30);
        $epgChannels = $source ? EpgChannel::where('epg_source_id', $source->id)->orderBy('name')->get() : collect();

        return view('admin.channels.epg', compact('source', 'channels', 'epgChannels'));
    }

    public function source(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:100', 'url' => 'required|url']);
        $source = EpgSource::firstOrCreate([], $data);
        $urlChanged = $source->exists && $source->url !== $data['url'];

        if ($urlChanged) {
            TvChannelEpg::where('epg_source_id', $source->id)->update(['epg_channel_id' => null]);
            EpgProgram::where('epg_source_id', $source->id)->delete();
            EpgChannel::where('epg_source_id', $source->id)->delete();
            $source->last_synced_at = null;
        }

        $source->fill($data)->save();

        return back()->with('success', $urlChanged
            ? 'Fonte EPG alterada. Os vínculos foram limpos; atualize a fonte e associe os canais novamente.'
            : 'Fonte EPG salva.');
    }

    public function sync(EpgSyncService $sync)
    {
        try {
            $source = EpgSource::where('is_active', true)->firstOrFail();
            $stats = $sync->sync($source);

            return back()->with('success', "EPG atualizado: {$stats['programs']} programas para {$stats['mapped']} canais mapeados.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Falha ao atualizar EPG: ' . $e->getMessage());
        }
    }

    public function mapping(Request $request, TvChannel $channel)
    {
        $data = $request->validate(['epg_channel_id' => 'nullable|exists:epg_channels,id']);
        $source = EpgSource::firstOrFail();

        TvChannelEpg::updateOrCreate(
            ['tv_channel_id' => $channel->id],
            ['epg_source_id' => $source->id, 'epg_channel_id' => $data['epg_channel_id'] ?? null]
        );

        return back()->with('success', 'Vínculo EPG atualizado.');
    }
}

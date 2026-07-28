<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChannelWatchParty;
use App\Models\TvChannel;
use App\Models\TvChannelLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChannelWatchPartyController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'channel_id' => ['required', 'integer', 'exists:tv_channels,id'],
            'source_link_id' => ['nullable', 'integer'],
        ]);
        $channel = TvChannel::findOrFail($data['channel_id']);
        $link = $this->resolveLink($channel, $data['source_link_id'] ?? null);
        $this->ensureAccess($request, $link);

        do { $code = Str::upper(Str::random(8)); } while (ChannelWatchParty::where('code', $code)->exists());
        $party = ChannelWatchParty::create([
            'code' => $code,
            'host_user_id' => $request->user()->id,
            'tv_channel_id' => $channel->id,
            'tv_channel_link_id' => $link?->id,
            'expires_at' => now()->addHours(12),
        ]);

        return response()->json($this->payload($party->load(['channel', 'sourceLink'])), 201);
    }

    public function show(Request $request, string $code)
    {
        $party = ChannelWatchParty::with(['channel', 'sourceLink'])
            ->where('code', Str::upper($code))->where('expires_at', '>', now())->firstOrFail();
        $this->ensureAccess($request, $party->sourceLink);
        return response()->json($this->payload($party));
    }

    public function updateSource(Request $request, string $code)
    {
        $data = $request->validate(['source_link_id' => ['nullable', 'integer']]);
        $party = ChannelWatchParty::with('channel')->where('code', Str::upper($code))->where('expires_at', '>', now())->firstOrFail();
        abort_unless($party->host_user_id === $request->user()->id, 403, 'Apenas o anfitri?o pode trocar a fonte da sala.');
        $link = $this->resolveLink($party->channel, $data['source_link_id'] ?? null);
        $this->ensureAccess($request, $link);
        $party->update(['tv_channel_link_id' => $link?->id]);
        return response()->json($this->payload($party->fresh()->load(['channel', 'sourceLink'])));
    }

    private function resolveLink(TvChannel $channel, ?int $linkId): ?TvChannelLink
    {
        if (!$linkId) return null;
        return $channel->links()->whereKey($linkId)->firstOrFail();
    }

    private function ensureAccess(Request $request, ?TvChannelLink $link): void
    {
        if ($link && $link->player_sub !== 'free' && !$request->user()->hasPlan()) abort(403, 'Seu plano n?o permite usar esta fonte.');
    }

    private function payload(ChannelWatchParty $party): array
    {
        return [
            'code' => $party->code,
            'channel' => ['id' => $party->channel->id, 'name' => $party->channel->name, 'image_url' => $party->channel->image_url],
            'source_link_id' => $party->tv_channel_link_id,
            'source_name' => $party->sourceLink?->name,
            'expires_at' => $party->expires_at,
            'is_host' => auth()->id() === $party->host_user_id,
        ];
    }
}

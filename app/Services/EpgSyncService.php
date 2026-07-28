<?php

namespace App\Services;

use App\Models\EpgChannel;
use App\Models\EpgProgram;
use App\Models\EpgSource;
use App\Models\TvChannel;
use App\Models\TvChannelEpg;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EpgSyncService
{
    public function sync(EpgSource $source): array
    {
        $response = Http::timeout(90)->accept('application/xml,text/xml,*/*')->get($source->url);
        if (!$response->successful()) throw new \RuntimeException('Feed EPG indispon?vel (HTTP ' . $response->status() . ').');
        $body = $response->body();
        if (substr($body, 0, 2) === "\x1f\x8b") {
            $decoded = gzdecode($body);
            if ($decoded === false) throw new \RuntimeException('N?o foi poss?vel descompactar o feed EPG.');
            $body = $decoded;
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        if (!$xml) throw new \RuntimeException('O feed EPG n?o cont?m XMLTV v?lido.');

        $catalogRows = [];
        foreach ($xml->channel as $channel) {
            $id = (string) $channel['id']; if (!$id) continue;
            $catalogRows[] = ['epg_source_id' => $source->id, 'xmltv_id' => $id, 'name' => (string) ($channel->{'display-name'}[0] ?? $id), 'icon_url' => isset($channel->icon[0]) ? (string) $channel->icon[0]['src'] : null, 'created_at' => now(), 'updated_at' => now()];
        }
        foreach (array_chunk($catalogRows, 500) as $chunk) {
            EpgChannel::upsert($chunk, ['epg_source_id', 'xmltv_id'], ['name', 'icon_url', 'updated_at']);
        }
        $catalog = EpgChannel::where('epg_source_id', $source->id)->pluck('id', 'xmltv_id')->all();
        $this->autoMap($source);
        $mapped = TvChannelEpg::with('epgChannel')->where('epg_source_id',$source->id)->whereNotNull('epg_channel_id')->get()->keyBy(fn($map) => $map->epgChannel?->xmltv_id);
        $channelIds = $mapped->mapWithKeys(fn($map,$xmltvId) => [$xmltvId=>$map->tv_channel_id])->all();
        EpgProgram::where('epg_source_id',$source->id)->where('ends_at','<',now()->subDay())->delete();

        $rows=[]; $count=0;
        foreach ($xml->programme as $programme) {
            $xmltvId=(string)$programme['channel']; if (!isset($channelIds[$xmltvId])) continue;
            $starts=$this->parseDate((string)$programme['start']); $ends=$this->parseDate((string)$programme['stop']);
            if (!$starts || !$ends || $ends->lessThan(now()->subHours(6))) continue;
            $rows[]=['epg_source_id'=>$source->id,'tv_channel_id'=>$channelIds[$xmltvId],'xmltv_channel_id'=>$xmltvId,'title'=>(string)($programme->title[0] ?? 'Sem t?tulo'),'description'=>(string)($programme->desc[0] ?? '') ?: null,'category'=>(string)($programme->category[0] ?? '') ?: null,'icon_url'=>isset($programme->icon[0])?(string)$programme->icon[0]['src']:null,'starts_at'=>$starts,'ends_at'=>$ends,'created_at'=>now(),'updated_at'=>now()];
            if (count($rows) === 500) { EpgProgram::upsert($rows,['epg_source_id','xmltv_channel_id','starts_at','ends_at'],['tv_channel_id','title','description','category','icon_url','updated_at']); $count+=count($rows); $rows=[]; }
        }
        if ($rows) { EpgProgram::upsert($rows,['epg_source_id','xmltv_channel_id','starts_at','ends_at'],['tv_channel_id','title','description','category','icon_url','updated_at']); $count+=count($rows); }
        $source->update(['last_synced_at'=>now(),'last_error'=>null]);
        return ['channels'=>count($catalog),'mapped'=>count($channelIds),'programs'=>$count];
    }

    private function autoMap(EpgSource $source): void
    {
        $catalog=EpgChannel::where('epg_source_id',$source->id)->get();
        $used=TvChannelEpg::where('epg_source_id',$source->id)->pluck('tv_channel_id');
        TvChannel::whereNotIn('id',$used)->get()->each(function(TvChannel $channel) use($source,$catalog) {
            $name=$this->normal($channel->name); $match=$catalog->first(fn($item) => $this->normal($item->name)===$name) ?? $catalog->first(fn($item) => strlen($name)>4 && (str_contains($this->normal($item->name),$name) || str_contains($name,$this->normal($item->name))));
            if ($match) TvChannelEpg::updateOrCreate(['tv_channel_id'=>$channel->id],['epg_source_id'=>$source->id,'epg_channel_id'=>$match->id]);
        });
    }

    private function normal(string $value): string { return preg_replace('/[^a-z0-9]/','',strtolower(Str::ascii($value))); }
    private function parseDate(string $value): ?Carbon
    {
        if (!preg_match('/^(\d{14})(?:\s*([+-]\d{4}))?/', trim($value), $matches)) return null;

        try {
            return Carbon::createFromFormat('YmdHis O', $matches[1] . ' ' . ($matches[2] ?? '+0000'))->utc();
        } catch (\Throwable) {
            return null;
        }
    }
}

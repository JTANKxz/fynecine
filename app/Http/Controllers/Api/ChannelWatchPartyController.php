<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\ChannelWatchParty;
use App\Models\ChannelWatchPartyMember;
use App\Models\TvChannel;
use App\Models\TvChannelLink;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChannelWatchPartyController extends Controller
{
 public function store(Request $request) { $data=$request->validate(['channel_id'=>['required','integer','exists:tv_channels,id'],'source_link_id'=>['nullable','integer']]); $channel=TvChannel::findOrFail($data['channel_id']); $link=$this->resolveLink($channel,$data['source_link_id']??null); $this->ensureAccess($request,$link); do{$code=Str::upper(Str::random(8));}while(ChannelWatchParty::where('code',$code)->exists()); $party=ChannelWatchParty::create(['code'=>$code,'host_user_id'=>$request->user()->id,'tv_channel_id'=>$channel->id,'tv_channel_link_id'=>$link?->id,'expires_at'=>now()->addHours(12),'state_updated_at'=>now()]); $this->touchMember($party,$request->user()->id); return response()->json($this->payload($party->fresh()),201); }
 public function show(Request $request,string $code) { $party=$this->findParty($code); $this->ensureAccess($request,$party->sourceLink); $this->touchMember($party,$request->user()->id); return response()->json($this->payload($party->fresh())); }
 public function updateSource(Request $request,string $code) { $data=$request->validate(['source_link_id'=>['nullable','integer']]); $party=$this->hostParty($request,$code); $link=$this->resolveLink($party->channel,$data['source_link_id']??null); $this->ensureAccess($request,$link); $party->update(['tv_channel_link_id'=>$link?->id,'playback_position'=>null,'state_updated_at'=>now()]); return response()->json($this->payload($party->fresh())); }
 public function updateChannel(Request $request,string $code) { $data=$request->validate(['channel_id'=>['required','integer','exists:tv_channels,id'],'source_link_id'=>['nullable','integer']]); $party=$this->hostParty($request,$code); $channel=TvChannel::findOrFail($data['channel_id']); $link=$this->resolveLink($channel,$data['source_link_id']??null); $this->ensureAccess($request,$link); $party->update(['tv_channel_id'=>$channel->id,'tv_channel_link_id'=>$link?->id,'playback_position'=>null,'state_updated_at'=>now()]); return response()->json($this->payload($party->fresh())); }
 public function updateState(Request $request,string $code) { $data=$request->validate(['is_playing'=>['required','boolean'],'playback_position'=>['nullable','numeric','min:0']]); $party=$this->hostParty($request,$code); $party->update(['is_playing'=>$data['is_playing'],'playback_position'=>$data['playback_position']??null,'state_updated_at'=>now()]); return response()->json($this->payload($party->fresh())); }
 public function leave(Request $request,string $code) { $party=$this->findParty($code); ChannelWatchPartyMember::where('channel_watch_party_id',$party->id)->where('user_id',$request->user()->id)->update(['left_at'=>now()]); return response()->json(['message'=>'Voc? saiu da Watch Party.']); }
 private function findParty(string $code): ChannelWatchParty { return ChannelWatchParty::with(['channel','sourceLink','members.user'])->where('code',Str::upper($code))->where('expires_at','>',now())->firstOrFail(); }
 private function hostParty(Request $request,string $code): ChannelWatchParty { $party=$this->findParty($code); abort_unless($party->host_user_id===$request->user()->id,403,'Apenas o anfitri?o pode alterar a sala.'); return $party; }
 private function resolveLink(TvChannel $channel,?int $id):?TvChannelLink { return $id?$channel->links()->whereKey($id)->firstOrFail():null; }
 private function ensureAccess(Request $request,?TvChannelLink $link):void { if($link && $link->player_sub!=='free' && !$request->user()->hasPlan()) abort(403,'Seu plano n?o permite usar esta fonte.'); }
 private function touchMember(ChannelWatchParty $party,int $userId):void { ChannelWatchPartyMember::updateOrCreate(['channel_watch_party_id'=>$party->id,'user_id'=>$userId],['joined_at'=>now(),'last_seen_at'=>now(),'left_at'=>null]); }
 private function payload(ChannelWatchParty $party):array { $party->loadMissing(['channel','sourceLink','members.user']); return ['code'=>$party->code,'channel'=>['id'=>$party->channel->id,'name'=>$party->channel->name,'image_url'=>$party->channel->image_url],'source_link_id'=>$party->tv_channel_link_id,'source_name'=>$party->sourceLink?->name,'expires_at'=>$party->expires_at,'is_host'=>auth()->id()===$party->host_user_id,'is_playing'=>$party->is_playing,'playback_position'=>$party->playback_position,'state_updated_at'=>$party->state_updated_at,'members'=>$party->members->whereNull('left_at')->map(fn($m)=>['id'=>$m->user_id,'name'=>$m->user->username?:$m->user->name,'avatar'=>$m->user->avatar,'is_host'=>$m->user_id===$party->host_user_id])->values()]; }
}

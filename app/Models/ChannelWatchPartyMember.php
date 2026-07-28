<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ChannelWatchPartyMember extends Model {
    protected $fillable = ['channel_watch_party_id','user_id','joined_at','last_seen_at','left_at'];
    protected $casts = ['joined_at'=>'datetime','last_seen_at'=>'datetime','left_at'=>'datetime'];
    public function user() { return $this->belongsTo(User::class); }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelWatchParty extends Model
{
    protected $fillable = ['code', 'host_user_id', 'tv_channel_id', 'tv_channel_link_id', 'is_playing', 'playback_position', 'state_updated_at', 'expires_at'];
    protected $casts = ['expires_at' => 'datetime', 'state_updated_at' => 'datetime', 'is_playing' => 'boolean', 'playback_position' => 'float'];

    public function channel() { return $this->belongsTo(TvChannel::class, 'tv_channel_id'); }
    public function sourceLink() { return $this->belongsTo(TvChannelLink::class, 'tv_channel_link_id'); }
    public function host() { return $this->belongsTo(User::class, 'host_user_id'); }
    public function members() { return $this->hasMany(ChannelWatchPartyMember::class); }
}

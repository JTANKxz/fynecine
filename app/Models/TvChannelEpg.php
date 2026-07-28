<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TvChannelEpg extends Model { protected $fillable=['tv_channel_id','epg_source_id','epg_channel_id']; public function epgChannel(){ return $this->belongsTo(EpgChannel::class); } }

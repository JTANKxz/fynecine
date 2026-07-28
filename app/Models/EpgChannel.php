<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EpgChannel extends Model { protected $fillable=['epg_source_id','xmltv_id','name','icon_url']; public function source(){ return $this->belongsTo(EpgSource::class,'epg_source_id'); } }

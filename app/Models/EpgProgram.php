<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EpgProgram extends Model { protected $fillable=['epg_source_id','tv_channel_id','xmltv_channel_id','title','description','category','icon_url','starts_at','ends_at']; protected $casts=['starts_at'=>'datetime','ends_at'=>'datetime']; }

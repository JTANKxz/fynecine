<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EpgSource extends Model { protected $fillable=['name','url','is_active','last_synced_at','last_error']; protected $casts=['is_active'=>'boolean','last_synced_at'=>'datetime']; public function channels(){ return $this->hasMany(EpgChannel::class); } }

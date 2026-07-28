<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void {
  $ids=DB::table('epg_sources')->where('name','IPTV-EPG Brasil')->pluck('id');
  if($ids->isNotEmpty()){ DB::table('tv_channel_epgs')->whereIn('epg_source_id',$ids)->update(['epg_channel_id'=>null]); DB::table('epg_programs')->whereIn('epg_source_id',$ids)->delete(); DB::table('epg_channels')->whereIn('epg_source_id',$ids)->delete(); }
  DB::table('epg_sources')->whereIn('id',$ids)->update(['name'=>'EPGShare Brasil 1','url'=>'https://epgshare01.online/epgshare01/epg_ripper_BR1.xml.gz','time_offset_minutes'=>0,'last_synced_at'=>null,'updated_at'=>now()]);
 }
 public function down(): void {}
};

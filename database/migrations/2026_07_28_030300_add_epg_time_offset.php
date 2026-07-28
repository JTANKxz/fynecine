<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::table('epg_sources', function(Blueprint $table){ $table->integer('time_offset_minutes')->default(0)->after('url'); }); DB::table('epg_sources')->where('name','IPTV-EPG Brasil')->update(['time_offset_minutes'=>-60]); }
 public function down(): void { Schema::table('epg_sources', function(Blueprint $table){ $table->dropColumn('time_offset_minutes'); }); }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $sourceIds = DB::table('epg_sources')
            ->where('url', 'https://raw.githubusercontent.com/globetvapp/epg/main/Brazil/brazil1.xml')
            ->pluck('id');

        if ($sourceIds->isNotEmpty()) {
            DB::table('tv_channel_epgs')->whereIn('epg_source_id', $sourceIds)->update(['epg_channel_id' => null]);
            DB::table('epg_programs')->whereIn('epg_source_id', $sourceIds)->delete();
            DB::table('epg_channels')->whereIn('epg_source_id', $sourceIds)->delete();
        }

        DB::table('epg_sources')
            ->whereIn('id', $sourceIds)
            ->update([
                'name' => 'IPTV-EPG Brasil',
                'url' => 'https://iptv-epg.org/files/epg-br.xml',
                'last_synced_at' => null,
                'last_error' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('epg_sources')
            ->where('url', 'https://iptv-epg.org/files/epg-br.xml')
            ->where('name', 'IPTV-EPG Brasil')
            ->update([
                'name' => 'GlobeTV Brasil (XMLTV)',
                'url' => 'https://raw.githubusercontent.com/globetvapp/epg/main/Brazil/brazil1.xml',
                'last_synced_at' => null,
                'updated_at' => now(),
            ]);
    }
};

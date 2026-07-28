<?php
namespace App\Console\Commands;
use App\Models\EpgSource;
use App\Services\EpgSyncService;
use Illuminate\Console\Command;
class SyncEpg extends Command { protected $signature='epg:sync'; protected $description='Atualiza a programacao EPG XMLTV'; public function handle(EpgSyncService $sync): int { foreach(EpgSource::where('is_active',true)->get() as $source){ $stats=$sync->sync($source); $this->info("{$source->name}: {$stats['programs']} programas"); } return self::SUCCESS; } }

<?php

namespace App\Console\Commands;

use App\Services\BrasileiraoScheduleService;
use Illuminate\Console\Command;

class SyncBrasileiraoGames extends Command
{
    protected $signature = 'football:sync-brasileirao';
    protected $description = 'Sincroniza os próximos jogos do Brasileirão Série A como eventos automáticos';

    public function handle(BrasileiraoScheduleService $schedule): int
    {
        $count = $schedule->syncUpcomingGames();
        $this->info("{$count} jogo(s) do Brasileirão sincronizado(s).");

        return self::SUCCESS;
    }
}

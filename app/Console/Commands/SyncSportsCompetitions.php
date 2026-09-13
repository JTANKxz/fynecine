<?php

namespace App\Console\Commands;

use App\Models\Championship;
use App\Services\Sports365Service;
use Illuminate\Console\Command;

class SyncSportsCompetitions extends Command
{
    protected $signature = 'sports:sync';
    protected $description = 'Sincroniza jogos futuros dos campeonatos esportivos ativados no painel';

    public function handle(Sports365Service $sports): int
    {
        $championships = Championship::query()
            ->where('is_sports_enabled', true)
            ->where('auto_sync', true)
            ->where('external_provider', '365scores')
            ->whereNotNull('external_id')
            ->get();

        $total = 0;
        foreach ($championships as $championship) {
            try {
                $count = $sports->syncUpcomingGames($championship);
                $total += $count;
                $this->line("{$championship->name}: {$count} jogo(s).");
            } catch (\Throwable $exception) {
                report($exception);
                $this->warn("{$championship->name}: falhou - {$exception->getMessage()}");
            }
        }

        $this->info("{$total} jogo(s) sincronizado(s) no total.");
        return self::SUCCESS;
    }
}

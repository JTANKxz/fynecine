<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:cleanup', function () {
    $count = \App\Models\Notification::where('expires_at', '<', now())->delete();
    $this->info("{$count} notificações expiradas foram removidas.");
})->purpose('Deleta notificações que já passaram da data de expiração');

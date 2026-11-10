<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Lembretes diarios de mensalidades (familias do portal).
Schedule::command('financeiro:lembrar-vencimentos')->dailyAt('07:00');

// Sincronizacao horaria das cobrancas no gateway (baixa/venecidas).
Schedule::command('cobrancas:sincronizar')->hourly();

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\ReservaService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    app(ReservaService::class)->processarReservasAtrasadasEAjustarSaldo();
})->everyMinute()->name('reservas-atraso');

Schedule::command('clockify:lembrete-horas')
    ->dailyAt('18:00')
    ->weekdays()
    ->name('lembrete-horas')
    ->withoutOverlapping();

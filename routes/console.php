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

// Trial vencido: marca 'expirado' e cancela a cobrança inicial pendente.
// (O acesso já é bloqueado antes disso via planoAtivo()/middleware —
// aqui é só a regularização do status.)
Schedule::call(function () {
    $expiradas = \App\Models\EmpresaPlano::where('status', 'trial')
        ->whereNotNull('data_vencimento')
        ->where('data_vencimento', '<', now()->startOfDay());

    \App\Models\EmpresaPlanoPagamento::whereIn('empresa_plano_id', (clone $expiradas)->pluck('id'))
        ->where('status', 'pendente')
        ->update(['status' => 'cancelado']);

    $expiradas->update(['status' => 'expirado']);
})->daily()->name('trials-expirados')->withoutOverlapping();

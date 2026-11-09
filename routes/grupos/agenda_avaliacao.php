<?php

use App\Http\Controllers\AgendaAvaliacaoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('agenda-avaliacoes', [AgendaAvaliacaoController::class, 'index'])
        ->name('avaliacao.agenda');
    Route::get('agenda-avaliacoes/filtro', [AgendaAvaliacaoController::class, 'filtro'])
        ->name('avaliacao_agenda.filtro');
    Route::match(['get', 'post'], 'agenda-avaliacoes/filtro/remover', [AgendaAvaliacaoController::class, 'removerFiltro'])
        ->name('avaliacao_agenda.removerFiltro');
});

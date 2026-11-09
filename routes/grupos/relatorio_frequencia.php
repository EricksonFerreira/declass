<?php

use App\Http\Controllers\RelatorioFrequenciaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('relatorio-frequencia/filtro', [RelatorioFrequenciaController::class, 'filtro'])
        ->name('relatorio_frequencia.filtro');
    Route::match(['get', 'post'], 'relatorio-frequencia/filtro/remover', [RelatorioFrequenciaController::class, 'removerFiltro'])
        ->name('relatorio_frequencia.removerFiltro');
    Route::post('relatorio-frequencia/per-page', [RelatorioFrequenciaController::class, 'setPerPage'])
        ->name('relatorio_frequencia.setPerPage');

    Route::get('relatorio-frequencia', [RelatorioFrequenciaController::class, 'index'])
        ->name('relatorio_frequencia.index');
});

<?php

use App\Http\Controllers\RelatorioInadimplenciaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('relatorio-inadimplencia/filtro', [RelatorioInadimplenciaController::class, 'filtro'])
        ->name('relatorio_inadimplencia.filtro');
    Route::match(['get', 'post'], 'relatorio-inadimplencia/filtro/remover', [RelatorioInadimplenciaController::class, 'removerFiltro'])
        ->name('relatorio_inadimplencia.removerFiltro');
    Route::post('relatorio-inadimplencia/per-page', [RelatorioInadimplenciaController::class, 'setPerPage'])
        ->name('relatorio_inadimplencia.setPerPage');

    Route::get('relatorio-inadimplencia', [RelatorioInadimplenciaController::class, 'index'])
        ->name('relatorio_inadimplencia.index');
});

<?php

use App\Http\Controllers\AnoLetivoController;
use Illuminate\Support\Facades\Route;

Route::prefix('ano-letivo')->name('ano_letivo.')->group(function () {
    Route::post('setperpage', [AnoLetivoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [AnoLetivoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [AnoLetivoController::class, 'filtro'])->name('filtro');
    Route::post('{id}/gerar-periodos', [AnoLetivoController::class, 'gerarPeriodos'])->name('gerar_periodos');
});
Route::resource('ano-letivo', AnoLetivoController::class)
    ->only(['index', 'show', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('ano_letivo');

<?php

use App\Http\Controllers\PeriodoLetivoController;
use Illuminate\Support\Facades\Route;

Route::prefix('periodo-letivo')->name('periodo_letivo.')->group(function () {
    Route::post('setperpage', [PeriodoLetivoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [PeriodoLetivoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [PeriodoLetivoController::class, 'filtro'])->name('filtro');
});
Route::resource('periodo-letivo', PeriodoLetivoController::class)
    ->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])
    ->names('periodo_letivo');

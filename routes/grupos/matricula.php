<?php

use App\Http\Controllers\MatriculaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('matriculas/filtro', [MatriculaController::class, 'filtro'])->name('matricula.filtro');
    Route::post('matriculas/filtro/remover', [MatriculaController::class, 'removerFiltro'])->name('matricula.removerFiltro');
    Route::post('matriculas/per-page', [MatriculaController::class, 'setPerPage'])->name('matricula.setPerPage');

    Route::get('matricula/rematricula', [MatriculaController::class, 'rematricula'])->name('matricula.rematricula');
    Route::post('matricula/rematricula', [MatriculaController::class, 'rematricular'])->name('matricula.rematricular');

    Route::resource('matricula', MatriculaController::class)->except(['show']);
});

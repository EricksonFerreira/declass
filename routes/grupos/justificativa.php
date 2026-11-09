<?php

use App\Http\Controllers\JustificativaController;
use Illuminate\Support\Facades\Route;

Route::group([], function () {
    Route::get('justificativas/filtro', [JustificativaController::class, 'filtro'])->name('justificativa.filtro');
    Route::match(['get', 'post'], 'justificativas/filtro/remover', [JustificativaController::class, 'removerFiltro'])->name('justificativa.removerFiltro');
    Route::post('justificativas/per-page', [JustificativaController::class, 'setPerPage'])->name('justificativa.setPerPage');

    Route::get('justificativas', [JustificativaController::class, 'index'])->name('justificativa.index');
    Route::get('justificativas/nova', [JustificativaController::class, 'create'])->name('justificativa.create');
    Route::post('justificativas', [JustificativaController::class, 'store'])->name('justificativa.store');
    Route::get('justificativas/{id}/arquivo', [JustificativaController::class, 'arquivo'])->name('justificativa.arquivo');
    Route::delete('justificativas/{id}', [JustificativaController::class, 'destroy'])->name('justificativa.destroy');
});

<?php

use App\Http\Controllers\DespesaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('despesas/filtro', [DespesaController::class, 'filtro'])->name('despesa.filtro');
    Route::post('despesas/filtro/remover', [DespesaController::class, 'removerFiltro'])->name('despesa.removerFiltro');
    Route::post('despesas/per-page', [DespesaController::class, 'setPerPage'])->name('despesa.setPerPage');

    Route::post('despesa/{id}/pagar', [DespesaController::class, 'pagar'])->name('despesa.pagar');
    Route::post('despesa/{id}/cancelar', [DespesaController::class, 'cancelar'])->name('despesa.cancelar');

    Route::resource('despesa', DespesaController::class)->except(['show']);
});

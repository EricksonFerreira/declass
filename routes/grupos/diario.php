<?php

use App\Http\Controllers\DiarioController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('diarios/filtro', [DiarioController::class, 'filtro'])->name('diario.filtro');
    Route::match(['get', 'post'], 'diarios/filtro/remover', [DiarioController::class, 'removerFiltro'])->name('diario.removerFiltro');
    Route::post('diarios/per-page', [DiarioController::class, 'setPerPage'])->name('diario.setPerPage');

    Route::get('diarios/{diario}/frequencia', [DiarioController::class, 'frequencia'])->name('diario.frequencia');
    Route::put('diarios/{diario}/frequencia', [DiarioController::class, 'salvarFrequencia'])->name('diario.frequencia.salvar');

    Route::resource('diario', DiarioController::class)->except(['show']);
});

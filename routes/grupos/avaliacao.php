<?php

use App\Http\Controllers\AvaliacaoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('avaliacoes/filtro', [AvaliacaoController::class, 'filtro'])->name('avaliacao.filtro');
    Route::match(['get', 'post'], 'avaliacoes/filtro/remover', [AvaliacaoController::class, 'removerFiltro'])->name('avaliacao.removerFiltro');
    Route::post('avaliacoes/per-page', [AvaliacaoController::class, 'setPerPage'])->name('avaliacao.setPerPage');

    Route::get('avaliacoes/{avaliacao}/notas', [AvaliacaoController::class, 'notas'])->name('avaliacao.notas');
    Route::put('avaliacoes/{avaliacao}/notas', [AvaliacaoController::class, 'salvarNotas'])->name('avaliacao.notas.salvar');

    Route::resource('avaliacao', AvaliacaoController::class)->except(['show']);
});

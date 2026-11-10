<?php

use App\Http\Controllers\MensalidadeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('mensalidade/filtro', [MensalidadeController::class, 'filtro'])->name('mensalidade.filtro');
    Route::match(['get', 'post'], 'mensalidade/filtro/remover', [MensalidadeController::class, 'removerFiltro'])->name('mensalidade.removerFiltro');
    Route::post('mensalidade/per-page', [MensalidadeController::class, 'setPerPage'])->name('mensalidade.setPerPage');

    Route::get('mensalidade', [MensalidadeController::class, 'index'])->name('mensalidade.index');
    Route::get('mensalidade/create', [MensalidadeController::class, 'create'])->name('mensalidade.create');
    Route::post('mensalidade', [MensalidadeController::class, 'store'])->name('mensalidade.store');
    Route::get('mensalidade/{id}/edit', [MensalidadeController::class, 'edit'])->name('mensalidade.edit');
    Route::put('mensalidade/{id}', [MensalidadeController::class, 'update'])->name('mensalidade.update');
    Route::delete('mensalidade/{id}', [MensalidadeController::class, 'destroy'])->name('mensalidade.destroy');
    Route::post('mensalidade/{id}/baixar', [MensalidadeController::class, 'baixar'])->name('mensalidade.baixar');
    Route::get('mensalidade/{id}/boleto', [MensalidadeController::class, 'boleto'])->name('mensalidade.boleto');
    Route::get('mensalidade/{id}/recibo', [MensalidadeController::class, 'recibo'])->name('mensalidade.recibo');
    Route::post('mensalidade/gerar', [MensalidadeController::class, 'gerar'])->name('mensalidade.gerar');
});
    Route::post('mensalidade/{id}/cobranca', [MensalidadeController::class, 'gerarCobranca'])->name('mensalidade.cobranca');
    Route::post('mensalidade/{id}/sincronizar', [MensalidadeController::class, 'sincronizarCobranca'])->name('mensalidade.sincronizar');

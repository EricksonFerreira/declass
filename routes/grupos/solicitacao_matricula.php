<?php

use App\Http\Controllers\SolicitacaoMatriculaController;
use Illuminate\Support\Facades\Route;

Route::prefix('solicitacao-matricula')->name('solicitacao_matricula.')->group(function () {
    Route::get('filtro', [SolicitacaoMatriculaController::class, 'filtro'])->name('filtro');
    Route::match(['get', 'post'], 'filtro/remover', [SolicitacaoMatriculaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::post('per-page', [SolicitacaoMatriculaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('create', [SolicitacaoMatriculaController::class, 'create'])->name('create');
    Route::post('/', [SolicitacaoMatriculaController::class, 'store'])->name('store');

    Route::get('/', [SolicitacaoMatriculaController::class, 'index'])->name('index');
    Route::get('{solicitacao_matricula}', [SolicitacaoMatriculaController::class, 'show'])->name('show');
    Route::get('{solicitacao_matricula}/edit', [SolicitacaoMatriculaController::class, 'edit'])->name('edit');
    Route::put('{solicitacao_matricula}', [SolicitacaoMatriculaController::class, 'update'])->name('update');
    Route::delete('{solicitacao_matricula}', [SolicitacaoMatriculaController::class, 'destroy'])->name('destroy');
    Route::post('{solicitacao_matricula}/aprovar', [SolicitacaoMatriculaController::class, 'aprovar'])->name('aprovar');
    Route::post('{solicitacao_matricula}/recusar', [SolicitacaoMatriculaController::class, 'recusar'])->name('recusar');
    Route::post('{solicitacao_matricula}/cancelar', [SolicitacaoMatriculaController::class, 'cancelar'])->name('cancelar');
});

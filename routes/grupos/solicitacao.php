<?php

use App\Http\Controllers\DocumentoSolicitacaoController;
use App\Http\Controllers\SolicitacaoController;
use App\Http\Controllers\TipoSolicitacaoController;
use Illuminate\Support\Facades\Route;

// Fila da secretaria: pedidos das familias (portal) e documentos solicitados
Route::prefix('solicitacoes')->name('solicitacao.')->group(function () {
    Route::get('filtro', [SolicitacaoController::class, 'filtro'])->name('filtro');
    Route::match(['get', 'post'], 'filtro/remover', [SolicitacaoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::post('per-page', [SolicitacaoController::class, 'setPerPage'])->name('setPerPage');

    Route::get('/', [SolicitacaoController::class, 'index'])->name('index');
    Route::put('{solicitacao}/responder', [SolicitacaoController::class, 'responder'])->name('responder');
    Route::post('{solicitacao}/mensagem', [SolicitacaoController::class, 'mensagem'])->name('mensagem');
    Route::get('{solicitacao}/anexos/{anexo}', [SolicitacaoController::class, 'anexo'])->name('anexo');
});

// Tipos de requerimento que a familia escolhe no portal (com anexo
// obrigatorio e campos extras definidos pela secretaria)
Route::resource('tipos-solicitacao', TipoSolicitacaoController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('tipo_solicitacao');

Route::prefix('documentos-solicitados')->name('documento_solicitacao.')->group(function () {
    Route::get('filtro', [DocumentoSolicitacaoController::class, 'filtro'])->name('filtro');
    Route::match(['get', 'post'], 'filtro/remover', [DocumentoSolicitacaoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::post('per-page', [DocumentoSolicitacaoController::class, 'setPerPage'])->name('setPerPage');

    Route::get('/', [DocumentoSolicitacaoController::class, 'index'])->name('index');
    Route::post('/', [DocumentoSolicitacaoController::class, 'store'])->name('store');
    Route::put('{documento_solicitacao}', [DocumentoSolicitacaoController::class, 'update'])->name('update');
    Route::get('{documento_solicitacao}/arquivo', [DocumentoSolicitacaoController::class, 'arquivo'])->name('arquivo');
});

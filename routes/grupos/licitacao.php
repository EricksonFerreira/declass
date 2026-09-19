<?php

use App\Http\Controllers\LicitacaoController;
use App\Http\Middleware\ExigirModuloLicitacoes;
use Illuminate\Support\Facades\Route;

Route::prefix('licitacoes')->name('licitacoes.')->middleware(ExigirModuloLicitacoes::class)->group(function () {
    Route::get('/', [LicitacaoController::class, 'index'])->name('index');
    Route::post('/', [LicitacaoController::class, 'store'])->name('store');
    Route::get('/{licitacao}', [LicitacaoController::class, 'show'])->name('show');
    Route::put('/{licitacao}', [LicitacaoController::class, 'update'])->name('update');
    Route::put('/{licitacao}/texto', [LicitacaoController::class, 'updateTexto'])->name('texto');
    Route::post('/{licitacao}/arquivo', [LicitacaoController::class, 'uploadArquivo'])->name('arquivo');
    Route::get('/{licitacao}/arquivo/{index}', [LicitacaoController::class, 'downloadArquivo'])->name('arquivo.download');
    Route::delete('/{licitacao}/arquivo/{index}', [LicitacaoController::class, 'excluirArquivo'])->name('arquivo.excluir');
    Route::patch('/{licitacao}/status', [LicitacaoController::class, 'updateStatus'])->name('status');
    Route::post('/{licitacao}/analisar', [LicitacaoController::class, 'analisar'])->name('analisar');
    Route::delete('/{licitacao}', [LicitacaoController::class, 'destroy'])->name('destroy');
});

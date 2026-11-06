<?php

use App\Http\Controllers\AlunoController;
use App\Http\Controllers\AlunoDocumentoController;
use Illuminate\Support\Facades\Route;

Route::prefix('aluno')->name('aluno.')->group(function () {
    Route::post('setperpage', [AlunoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [AlunoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [AlunoController::class, 'filtro'])->name('filtro');

    // Documentos do aluno (anexos digitais)
    Route::post('{aluno}/documentos', [AlunoDocumentoController::class, 'store'])->name('documento.store');
    Route::put('{aluno}/documentos/{documento}', [AlunoDocumentoController::class, 'update'])->name('documento.update');
    Route::delete('{aluno}/documentos/{documento}', [AlunoDocumentoController::class, 'destroy'])->name('documento.destroy');
    Route::get('{aluno}/documentos/{documento}/arquivo', [AlunoDocumentoController::class, 'arquivo'])->name('documento.arquivo');

    // Vinculos aluno <-> responsavel (papeis e autorizacoes)
    Route::post('{aluno}/responsaveis', [AlunoController::class, 'storeResponsavel'])->name('responsavel.store');
    Route::put('{aluno}/responsaveis/{vinculo}', [AlunoController::class, 'updateResponsavel'])->name('responsavel.update');
    Route::delete('{aluno}/responsaveis/{vinculo}', [AlunoController::class, 'destroyResponsavel'])->name('responsavel.destroy');
});
Route::resource('aluno', AlunoController::class);

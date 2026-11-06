<?php

use App\Http\Controllers\AlunoController;
use Illuminate\Support\Facades\Route;

Route::prefix('aluno')->name('aluno.')->group(function () {
    Route::post('setperpage', [AlunoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [AlunoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [AlunoController::class, 'filtro'])->name('filtro');

    // Vinculos aluno <-> responsavel (papeis e autorizacoes)
    Route::post('{aluno}/responsaveis', [AlunoController::class, 'storeResponsavel'])->name('responsavel.store');
    Route::put('{aluno}/responsaveis/{vinculo}', [AlunoController::class, 'updateResponsavel'])->name('responsavel.update');
    Route::delete('{aluno}/responsaveis/{vinculo}', [AlunoController::class, 'destroyResponsavel'])->name('responsavel.destroy');
});
Route::resource('aluno', AlunoController::class);

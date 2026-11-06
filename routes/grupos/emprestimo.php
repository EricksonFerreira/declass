<?php

use App\Http\Controllers\EmprestimoLivroController;
use Illuminate\Support\Facades\Route;

Route::prefix('emprestimo')->name('emprestimo.')->group(function () {
    Route::post('setperpage', [EmprestimoLivroController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [EmprestimoLivroController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [EmprestimoLivroController::class, 'filtro'])->name('filtro');
    Route::post('{emprestimo}/devolver', [EmprestimoLivroController::class, 'devolver'])->name('devolver');
});
Route::resource('emprestimo', EmprestimoLivroController::class)
    ->only(['index', 'create', 'store', 'destroy']);

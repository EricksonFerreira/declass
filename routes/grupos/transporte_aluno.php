<?php

use App\Http\Controllers\TransporteAlunoController;
use Illuminate\Support\Facades\Route;

Route::prefix('transporte-aluno')->name('transporte_aluno.')->group(function () {
    Route::post('setperpage', [TransporteAlunoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TransporteAlunoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TransporteAlunoController::class, 'filtro'])->name('filtro');
});
Route::resource('transporte-aluno', TransporteAlunoController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('transporte_aluno');

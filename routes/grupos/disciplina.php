<?php

use App\Http\Controllers\DisciplinaController;
use Illuminate\Support\Facades\Route;

Route::prefix('disciplina')->name('disciplina.')->group(function () {
    Route::post('setperpage', [DisciplinaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [DisciplinaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [DisciplinaController::class, 'filtro'])->name('filtro');
});
Route::resource('disciplina', DisciplinaController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

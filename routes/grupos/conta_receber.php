<?php

use App\Http\Controllers\ContaReceberController;
use Illuminate\Support\Facades\Route;

Route::prefix('conta-receber')->name('conta_receber.')->group(function () {
    Route::post('setperpage', [ContaReceberController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [ContaReceberController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [ContaReceberController::class, 'filtro'])->name('filtro');

    Route::post('{id}/receber', [ContaReceberController::class, 'receber'])->name('receber');
    Route::post('{id}/cancelar', [ContaReceberController::class, 'cancelar'])->name('cancelar');
});
Route::resource('conta-receber', ContaReceberController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('conta_receber');

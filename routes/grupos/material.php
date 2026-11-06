<?php

use App\Http\Controllers\MaterialController;
use Illuminate\Support\Facades\Route;

Route::prefix('material')->name('material.')->group(function () {
    Route::post('setperpage', [MaterialController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [MaterialController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [MaterialController::class, 'filtro'])->name('filtro');
    Route::get('{material}/movimentacoes', [MaterialController::class, 'movimentacoes'])->name('movimentacoes');
    Route::post('{material}/movimentar', [MaterialController::class, 'movimentar'])->name('movimentar');
});
Route::resource('material', MaterialController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('material');

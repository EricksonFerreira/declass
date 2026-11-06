<?php

use App\Http\Controllers\AvisoController;
use Illuminate\Support\Facades\Route;

Route::prefix('aviso')->name('aviso.')->group(function () {
    Route::post('setperpage', [AvisoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [AvisoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [AvisoController::class, 'filtro'])->name('filtro');
});
Route::resource('aviso', AvisoController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

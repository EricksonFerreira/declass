<?php

use App\Http\Controllers\RotaTransporteController;
use Illuminate\Support\Facades\Route;

Route::prefix('rota-transporte')->name('rota_transporte.')->group(function () {
    Route::post('setperpage', [RotaTransporteController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [RotaTransporteController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [RotaTransporteController::class, 'filtro'])->name('filtro');
});
Route::resource('rota-transporte', RotaTransporteController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('rota_transporte');

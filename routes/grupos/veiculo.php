<?php

use App\Http\Controllers\VeiculoController;
use Illuminate\Support\Facades\Route;

Route::prefix('veiculo')->name('veiculo.')->group(function () {
    Route::post('setperpage', [VeiculoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [VeiculoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [VeiculoController::class, 'filtro'])->name('filtro');
});
Route::resource('veiculo', VeiculoController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('veiculo');

<?php

use App\Http\Controllers\CardapioController;
use Illuminate\Support\Facades\Route;

Route::prefix('cardapio')->name('cardapio.')->group(function () {
    Route::post('setperpage', [CardapioController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [CardapioController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [CardapioController::class, 'filtro'])->name('filtro');
});
Route::resource('cardapio', CardapioController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('cardapio');

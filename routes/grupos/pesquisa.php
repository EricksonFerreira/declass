<?php

use App\Http\Controllers\PesquisaController;
use Illuminate\Support\Facades\Route;

Route::prefix('pesquisa')->name('pesquisa.')->group(function () {
    Route::post('setperpage', [PesquisaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [PesquisaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [PesquisaController::class, 'filtro'])->name('filtro');
    Route::get('{pesquisa}/resultados', [PesquisaController::class, 'resultados'])->name('resultados');
});
Route::resource('pesquisa', PesquisaController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('pesquisa');

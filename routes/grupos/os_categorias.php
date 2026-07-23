<?php

use App\Http\Controllers\OsCategoriaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('os-categorias/setperpage', [OsCategoriaController::class, 'setPerPage'])->name('os-categorias.setPerPage');
    Route::get('os-categorias/removerfiltro', [OsCategoriaController::class, 'removerFiltro'])->name('os-categorias.removerFiltro');
    Route::get('os-categorias/filtro', [OsCategoriaController::class, 'filtro'])->name('os-categorias.filtro');

    Route::resource('os-categorias', OsCategoriaController::class);
});

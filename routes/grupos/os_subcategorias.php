<?php

use App\Http\Controllers\OsSubcategoriaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('os-subcategorias/setperpage', [OsSubcategoriaController::class, 'setPerPage'])->name('os-subcategorias.setPerPage');
    Route::get('os-subcategorias/removerfiltro', [OsSubcategoriaController::class, 'removerFiltro'])->name('os-subcategorias.removerFiltro');
    Route::get('os-subcategorias/filtro', [OsSubcategoriaController::class, 'filtro'])->name('os-subcategorias.filtro');

    Route::resource('os-subcategorias', OsSubcategoriaController::class);
});

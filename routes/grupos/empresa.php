<?php

use App\Http\Controllers\EmpresaController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:gerenciar-empresa')->prefix('empresa')->name('empresa.')->group(function () {
    Route::post('setperpage', [EmpresaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [EmpresaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [EmpresaController::class, 'filtro'])->name('filtro');
});

Route::middleware('can:gerenciar-empresa')->group(function () {
    Route::resource('empresa', EmpresaController::class);
});

<?php

use App\Http\Controllers\ResponsavelController;
use Illuminate\Support\Facades\Route;

Route::prefix('responsavel')->name('responsavel.')->group(function () {
    Route::post('setperpage', [ResponsavelController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [ResponsavelController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [ResponsavelController::class, 'filtro'])->name('filtro');
});
Route::resource('responsavel', ResponsavelController::class);

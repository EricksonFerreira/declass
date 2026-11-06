<?php

use App\Http\Controllers\EscolaController;
use Illuminate\Support\Facades\Route;

Route::prefix('escola')->name('escola.')->group(function () {
    Route::post('setperpage', [EscolaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [EscolaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [EscolaController::class, 'filtro'])->name('filtro');
});
Route::resource('escola', EscolaController::class);

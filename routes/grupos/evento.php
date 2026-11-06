<?php

use App\Http\Controllers\EventoController;
use Illuminate\Support\Facades\Route;

Route::prefix('evento')->name('evento.')->group(function () {
    Route::post('setperpage', [EventoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [EventoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [EventoController::class, 'filtro'])->name('filtro');
});
Route::resource('evento', EventoController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('evento');

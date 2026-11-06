<?php

use App\Http\Controllers\EtapaController;
use Illuminate\Support\Facades\Route;

Route::prefix('etapa')->name('etapa.')->group(function () {
    Route::post('setperpage', [EtapaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [EtapaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [EtapaController::class, 'filtro'])->name('filtro');
});
Route::resource('etapa', EtapaController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

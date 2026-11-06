<?php

use App\Http\Controllers\PlanoMensalidadeController;
use Illuminate\Support\Facades\Route;

Route::prefix('plano-mensalidade')->name('plano_mensalidade.')->group(function () {
    Route::post('setperpage', [PlanoMensalidadeController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [PlanoMensalidadeController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [PlanoMensalidadeController::class, 'filtro'])->name('filtro');
});
Route::resource('plano-mensalidade', PlanoMensalidadeController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('plano_mensalidade');

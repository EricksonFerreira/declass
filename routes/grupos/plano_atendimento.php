<?php

use App\Http\Controllers\PlanoAtendimentoController;
use Illuminate\Support\Facades\Route;

Route::prefix('plano-atendimento')->name('plano_atendimento.')->group(function () {
    Route::post('setperpage', [PlanoAtendimentoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [PlanoAtendimentoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [PlanoAtendimentoController::class, 'filtro'])->name('filtro');
});
Route::resource('plano-atendimento', PlanoAtendimentoController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
    ->names('plano_atendimento');

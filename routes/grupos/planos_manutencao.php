<?php

use App\Http\Controllers\PlanoManutencaoPreventivaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::post('planos-manutencao/setperpage', [PlanoManutencaoPreventivaController::class, 'setPerPage'])->name('manutencao.planos.setPerPage');
    Route::get('planos-manutencao/removerfiltro', [PlanoManutencaoPreventivaController::class, 'removerFiltro'])->name('manutencao.planos.removerFiltro');
    Route::get('planos-manutencao/filtro', [PlanoManutencaoPreventivaController::class, 'filtro'])->name('manutencao.planos.filtro');

    Route::resource('planos-manutencao', PlanoManutencaoPreventivaController::class)
        ->names('manutencao.planos')
        ->parameters(['planos-manutencao' => 'plano']);
});

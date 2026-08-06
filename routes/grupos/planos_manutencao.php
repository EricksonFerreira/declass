<?php

use App\Http\Controllers\ItemManutencaoPreventivaController;
use App\Http\Controllers\ManutencaoPreventivaController;
use App\Http\Controllers\PlanoManutencaoPreventivaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('itens-manutencao-preventiva', ItemManutencaoPreventivaController::class)
        ->names('manutencao.itens')
        ->parameters(['itens-manutencao-preventiva' => 'item_manutencao']);
    Route::post('itens-manutencao-preventiva/setperpage', [ItemManutencaoPreventivaController::class, 'setPerPage'])->name('manutencao.itens.setPerPage');

    Route::post('planos-manutencao/setperpage', [PlanoManutencaoPreventivaController::class, 'setPerPage'])->name('manutencao.planos.setPerPage');
    Route::get('planos-manutencao/removerfiltro', [PlanoManutencaoPreventivaController::class, 'removerFiltro'])->name('manutencao.planos.removerFiltro');
    Route::get('planos-manutencao/filtro', [PlanoManutencaoPreventivaController::class, 'filtro'])->name('manutencao.planos.filtro');
    Route::get('planos-manutencao/buscar-itens', [PlanoManutencaoPreventivaController::class, 'buscarItens'])->name('manutencao.planos.buscarItens');
    Route::get('manutencao-preventiva', [ManutencaoPreventivaController::class, 'index'])->name('manutencao.preventivas.index');

    Route::resource('planos-manutencao', PlanoManutencaoPreventivaController::class)
        ->names('manutencao.planos')
        ->parameters(['planos-manutencao' => 'plano']);
});

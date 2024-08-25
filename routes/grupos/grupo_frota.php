<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GrupoFrotaController;

Route::prefix('grupo_frota')->name('grupo_frota.')->group(function () {

    Route::get('getModelosPorGrupoFrota', [GrupoFrotaController::class, 'getModelosPorGrupoFrota'])
        ->name('getModelosPorGrupoFrota');

    Route::post('setperpage', [GrupoFrotaController::class, 'setPerPage'])
        ->name('setPerPage');

    Route::get('removerfiltro', [GrupoFrotaController::class, 'removerFiltro'])
        ->name('removerFiltro');

    Route::get('filtro', [GrupoFrotaController::class, 'filtro'])
        ->name('filtro');

    Route::get('{id}/horariosdiarios', [GrupoFrotaController::class, 'horarios_diarios'])
        ->name('horarios_diarios');

    Route::post('{id}/horariosdiarios', [GrupoFrotaController::class, 'salvar_horarios_diarios'])
        ->name('salvar_horarios_diarios');
});
Route::resource('grupo_frota', GrupoFrotaController::class);

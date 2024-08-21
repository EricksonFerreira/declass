<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CaracteristicaGrupoFrotaController;

Route::prefix('caracteristica_grupo_frota')->name('caracteristica_grupo_frota.')->group(function () {
    Route::post('setperpage', [CaracteristicaGrupoFrotaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [CaracteristicaGrupoFrotaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [CaracteristicaGrupoFrotaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [CaracteristicaGrupoFrotaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [CaracteristicaGrupoFrotaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('caracteristica_grupo_frota', CaracteristicaGrupoFrotaController::class);

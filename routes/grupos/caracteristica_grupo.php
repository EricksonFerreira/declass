<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CaracteristicaGrupoController;

Route::prefix('caracteristica_grupo')->name('caracteristica_grupo.')->group(function () {
    Route::post('setperpage', [CaracteristicaGrupoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [CaracteristicaGrupoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [CaracteristicaGrupoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [CaracteristicaGrupoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [CaracteristicaGrupoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('caracteristica_grupo', CaracteristicaGrupoController::class);
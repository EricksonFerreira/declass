<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpcionalGrupoVeiculoController;

Route::prefix('opcional_grupo_veiculo')->name('opcional_grupo_veiculo.')->group(function () {
    Route::post('setperpage', [OpcionalGrupoVeiculoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [OpcionalGrupoVeiculoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [OpcionalGrupoVeiculoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [OpcionalGrupoVeiculoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [OpcionalGrupoVeiculoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('opcional_grupo_veiculo', OpcionalGrupoVeiculoController::class);
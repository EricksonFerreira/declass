<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VeiculoController;

Route::prefix('veiculo')->name('veiculo.')->group(function () {
    Route::post('setperpage', [VeiculoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [VeiculoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [VeiculoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [VeiculoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [VeiculoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('veiculo', VeiculoController::class);
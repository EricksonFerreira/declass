<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusVeiculoController;

Route::prefix('status_veiculo')->name('status_veiculo.')->group(function () {
    Route::post('setperpage', [StatusVeiculoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [StatusVeiculoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [StatusVeiculoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [StatusVeiculoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [StatusVeiculoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('status_veiculo', StatusVeiculoController::class);
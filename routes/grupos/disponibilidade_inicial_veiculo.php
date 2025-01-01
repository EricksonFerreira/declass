<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DisponibilidadeInicialVeiculoController;

Route::prefix('disponibilidade_inicial_veiculo')->name('disponibilidade_inicial_veiculo.')->group(function () {
    Route::post('setperpage', [DisponibilidadeInicialVeiculoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [DisponibilidadeInicialVeiculoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [DisponibilidadeInicialVeiculoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [DisponibilidadeInicialVeiculoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [DisponibilidadeInicialVeiculoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('disponibilidade_inicial_veiculo', DisponibilidadeInicialVeiculoController::class);
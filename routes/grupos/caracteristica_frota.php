<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CaracteristicaFrotaController;

Route::prefix('caracteristica_frota')->name('caracteristica_frota.')->group(function () {
    Route::post('setperpage', [CaracteristicaFrotaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [CaracteristicaFrotaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [CaracteristicaFrotaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [CaracteristicaFrotaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [CaracteristicaFrotaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('caracteristica_frota', CaracteristicaFrotaController::class);

<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrotaController;

Route::prefix('frota')->name('frota.')->group(function () {
    Route::post('setperpage', [FrotaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [FrotaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [FrotaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [FrotaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [FrotaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('frota', FrotaController::class);

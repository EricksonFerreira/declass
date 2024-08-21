<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipoFrotaController;

Route::prefix('tipo_frota')->name('tipo_frota.')->group(function () {
    Route::post('setperpage', [TipoFrotaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TipoFrotaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TipoFrotaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [TipoFrotaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [TipoFrotaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('tipo_frota', TipoFrotaController::class);

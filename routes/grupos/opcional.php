<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OpcionalController;

Route::prefix('opcional')->name('opcional.')->group(function () {
    Route::post('setperpage', [OpcionalController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [OpcionalController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [OpcionalController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [OpcionalController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [OpcionalController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('opcional', OpcionalController::class);
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipoOpcionalController;

Route::prefix('tipo_opcional')->name('tipo_opcional.')->group(function () {
    Route::post('setperpage', [TipoOpcionalController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TipoOpcionalController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TipoOpcionalController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [TipoOpcionalController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [TipoOpcionalController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('tipo_opcional', TipoOpcionalController::class);
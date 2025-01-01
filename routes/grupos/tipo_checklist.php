<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipoChecklistController;

Route::prefix('tipo_checklist')->name('tipo_checklist.')->group(function () {
    Route::post('setperpage', [TipoChecklistController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TipoChecklistController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TipoChecklistController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [TipoChecklistController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [TipoChecklistController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('tipo_checklist', TipoChecklistController::class);
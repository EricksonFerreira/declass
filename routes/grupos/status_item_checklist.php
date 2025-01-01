<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusItemChecklistController;

Route::prefix('status_item_checklist')->name('status_item_checklist.')->group(function () {
    Route::post('setperpage', [StatusItemChecklistController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [StatusItemChecklistController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [StatusItemChecklistController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [StatusItemChecklistController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [StatusItemChecklistController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('status_item_checklist', StatusItemChecklistController::class);
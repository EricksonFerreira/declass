<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemChecklistController;

Route::prefix('item_checklist')->name('item_checklist.')->group(function () {
    Route::post('setperpage', [ItemChecklistController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [ItemChecklistController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [ItemChecklistController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [ItemChecklistController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [ItemChecklistController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('item_checklist', ItemChecklistController::class);
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChecklistController;

Route::prefix('checklist')->name('checklist.')->group(function () {
    Route::post('setperpage', [ChecklistController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [ChecklistController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [ChecklistController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [ChecklistController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [ChecklistController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('checklist', ChecklistController::class);
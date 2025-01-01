<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConfiguracaoChecklistController;

Route::prefix('configuracao-checklist')->name('configuracao_checklist.')->group(function () {
    Route::post('setperpage', [ConfiguracaoChecklistController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [ConfiguracaoChecklistController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [ConfiguracaoChecklistController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [ConfiguracaoChecklistController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [ConfiguracaoChecklistController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('configuracao-checklist', ConfiguracaoChecklistController::class);

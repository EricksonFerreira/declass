<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstacaoController;

Route::prefix('estacao')->name('estacao.')->group(function () {
    Route::post('setperpage', [EstacaoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [EstacaoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [EstacaoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [EstacaoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [EstacaoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('estacao', EstacaoController::class);

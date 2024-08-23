<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovimentacaoController;

Route::prefix('movimentacao')->name('movimentacao.')->group(function () {
    Route::post('setperpage', [MovimentacaoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [MovimentacaoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [MovimentacaoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [MovimentacaoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [MovimentacaoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('movimentacao', MovimentacaoController::class);
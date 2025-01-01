<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusMovimentacaoController;

Route::prefix('status_movimentacao')->name('status_movimentacao.')->group(function () {
    Route::post('setperpage', [StatusMovimentacaoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [StatusMovimentacaoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [StatusMovimentacaoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [StatusMovimentacaoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [StatusMovimentacaoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('status_movimentacao', StatusMovimentacaoController::class);
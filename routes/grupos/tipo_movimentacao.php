<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipoMovimentacaoController;

Route::prefix('tipo_movimentacao')->name('tipo_movimentacao.')->group(function () {
    Route::post('setperpage', [TipoMovimentacaoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [TipoMovimentacaoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [TipoMovimentacaoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [TipoMovimentacaoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [TipoMovimentacaoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('tipo_movimentacao', TipoMovimentacaoController::class);
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CupomDescontoController;

Route::prefix('cupom_desconto')->name('cupom_desconto.')->group(function () {
    Route::post('setperpage', [CupomDescontoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [CupomDescontoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [CupomDescontoController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [CupomDescontoController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [CupomDescontoController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('cupom_desconto', CupomDescontoController::class);
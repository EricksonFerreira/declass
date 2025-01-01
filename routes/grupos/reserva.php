<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaController;

Route::prefix('reserva')->name('reserva.')->group(function () {
    Route::get('obterOpcionais', [ReservaController::class, 'obterOpcionais'])->name('obterOpcionais');
    Route::get('veiculosDisponiveis', [ReservaController::class, 'veiculosDisponiveis'])->name('veiculosDisponiveis');
    Route::post('setperpage', [ReservaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [ReservaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [ReservaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [ReservaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [ReservaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
    Route::get('{id}/efetuar-pagamento', [ReservaController::class, 'efetuarPagamento'])->name('efetuarPagamento');
    Route::post('{id}/processar-pagamento', [ReservaController::class, 'processarPagamento'])->name('processarPagamento');
    Route::get('{id}/cancelar', [ReservaController::class, 'cancelar'])->name('cancelar');

});
Route::resource('reserva', ReservaController::class);

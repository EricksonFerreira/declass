<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StatusReservaController;

Route::prefix('status_reserva')->name('status_reserva.')->group(function () {
    Route::post('setperpage', [StatusReservaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [StatusReservaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [StatusReservaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [StatusReservaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [StatusReservaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('status_reserva', StatusReservaController::class);
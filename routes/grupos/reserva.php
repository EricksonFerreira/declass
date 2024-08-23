<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservaController;

Route::prefix('reserva')->name('reserva.')->group(function () {
    Route::post('setperpage', [ReservaController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [ReservaController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [ReservaController::class, 'filtro'])->name('filtro');
    Route::get('{id}/horariosdiarios', [ReservaController::class, 'horarios_diarios'])->name('horarios_diarios');
    Route::post('{id}/horariosdiarios', [ReservaController::class, 'salvar_horarios_diarios'])->name('salvar_horarios_diarios');
});
Route::resource('reserva', ReservaController::class);
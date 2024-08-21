<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HorarioDiarioController;

Route::prefix('horario_diario')->name('horario_diario.')->group(function () {
    Route::post('setperpage', [HorarioDiarioController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [HorarioDiarioController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [HorarioDiarioController::class, 'filtro'])->name('filtro');
});
Route::resource('horario_diario', HorarioDiarioController::class);

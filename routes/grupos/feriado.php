<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FeriadoController;

Route::prefix('feriado')->name('feriado.')->group(function () {
    Route::post('setperpage', [FeriadoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [FeriadoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [FeriadoController::class, 'filtro'])->name('filtro');
});
Route::resource('feriado', FeriadoController::class);

<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaisController;

Route::prefix('pais')->name('pais.')->group(function () {
    Route::post('setperpage', [PaisController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [PaisController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [PaisController::class, 'filtro'])->name('filtro');
});
Route::resource('pais', PaisController::class);

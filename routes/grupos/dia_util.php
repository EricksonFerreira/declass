<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiaUtilController;

Route::prefix('dia_util')->name('dia_util.')->group(function () {
    Route::post('setperpage', [DiaUtilController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [DiaUtilController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [DiaUtilController::class, 'filtro'])->name('filtro');
});
Route::resource('dia_util', DiaUtilController::class);

<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegraController;

Route::middleware('can:acessar-superadmin')->prefix('regra')->name('regra.')->group(function () {
    Route::post('setperpage', [RegraController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [RegraController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [RegraController::class, 'filtro'])->name('filtro');
});
Route::middleware('can:acessar-superadmin')->group(function () {
    Route::resource('regra', RegraController::class);
});

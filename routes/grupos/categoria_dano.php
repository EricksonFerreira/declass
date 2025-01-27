<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoriaDanoController;

Route::prefix('categoria-dano')->name('categoria_dano.')->group(function () {
    Route::post('setperpage', [CategoriaDanoController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [CategoriaDanoController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [CategoriaDanoController::class, 'filtro'])->name('filtro');
});
Route::resource('categoria-dano', CategoriaDanoController::class)->names('categoria_dano');

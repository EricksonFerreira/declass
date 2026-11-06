<?php

use App\Http\Controllers\LivroController;
use Illuminate\Support\Facades\Route;

Route::prefix('livro')->name('livro.')->group(function () {
    Route::post('setperpage', [LivroController::class, 'setPerPage'])->name('setPerPage');
    Route::get('removerfiltro', [LivroController::class, 'removerFiltro'])->name('removerFiltro');
    Route::get('filtro', [LivroController::class, 'filtro'])->name('filtro');
});
Route::resource('livro', LivroController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

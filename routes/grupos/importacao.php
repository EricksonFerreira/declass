<?php

use App\Http\Controllers\ImportacaoController;
use Illuminate\Support\Facades\Route;

Route::prefix('importacao')->name('importacao.')->group(function () {
    Route::get('/', [ImportacaoController::class, 'index'])->name('index');
    Route::post('/{tipo}/preview', [ImportacaoController::class, 'preview'])->name('preview');
    Route::post('/{tipo}/importar', [ImportacaoController::class, 'importar'])->name('importar');
});

<?php

use App\Http\Controllers\ConfiguracaoIaController;
use Illuminate\Support\Facades\Route;

Route::prefix('configuracoes/ia')->name('configuracao-ia.')->group(function () {
    Route::get('/', [ConfiguracaoIaController::class, 'edit'])->name('edit');
    Route::put('/', [ConfiguracaoIaController::class, 'update'])->name('update');
});

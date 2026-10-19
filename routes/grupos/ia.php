<?php

use App\Http\Controllers\ConfiguracaoIaController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:acessar-superadmin')->prefix('configuracoes/ia')->name('configuracao-ia.')->group(function () {
    Route::get('/', [ConfiguracaoIaController::class, 'edit'])->name('edit');
});

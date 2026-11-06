<?php

use App\Http\Controllers\FluxoCaixaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('fluxo-caixa', [FluxoCaixaController::class, 'index'])->name('fluxo_caixa.index');
});

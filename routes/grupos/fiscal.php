<?php

use App\Http\Controllers\FiscalController;
use Illuminate\Support\Facades\Route;

Route::prefix('fiscal')->name('fiscal.')->group(function () {
    Route::get('/', [FiscalController::class, 'dashboard'])->name('dashboard');
    Route::get('/operacoes', [FiscalController::class, 'operacoes'])->name('operacoes');
    Route::get('/regras', [FiscalController::class, 'regras'])->name('regras');
    Route::get('/creditos', [FiscalController::class, 'creditos'])->name('creditos');
});

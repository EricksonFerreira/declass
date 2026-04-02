<?php

use App\Http\Controllers\FinanceiroController;
use Illuminate\Support\Facades\Route;

Route::prefix('financeiro')->name('financeiro.')->group(function () {
    Route::get('/', [FinanceiroController::class, 'index'])->name('index');
    Route::get('/reservas', [FinanceiroController::class, 'reservas'])->name('reservas');
});

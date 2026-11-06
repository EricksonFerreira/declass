<?php

use App\Http\Controllers\RelatorioTransporteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('relatorio-transporte', [RelatorioTransporteController::class, 'index'])
        ->name('relatorio_transporte.index');
});

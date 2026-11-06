<?php

use App\Http\Controllers\RelatorioFrequenciaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('relatorio-frequencia', [RelatorioFrequenciaController::class, 'index'])
        ->name('relatorio_frequencia.index');
});

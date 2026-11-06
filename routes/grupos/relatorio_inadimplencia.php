<?php

use App\Http\Controllers\RelatorioInadimplenciaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('relatorio-inadimplencia', [RelatorioInadimplenciaController::class, 'index'])
        ->name('relatorio_inadimplencia.index');
});

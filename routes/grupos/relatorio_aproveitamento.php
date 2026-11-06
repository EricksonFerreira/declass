<?php

use App\Http\Controllers\RelatorioAproveitamentoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('relatorio-aproveitamento', [RelatorioAproveitamentoController::class, 'index'])
        ->name('relatorio_aproveitamento.index');
});

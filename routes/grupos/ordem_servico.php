<?php

use App\Http\Controllers\OrdemServicoController;
use Illuminate\Support\Facades\Route;

Route::prefix('ordem-servico')->name('ordem-servico.')->group(function () {
    Route::get('danos-pendentes', [OrdemServicoController::class, 'danosPendentes'])->name('danos-pendentes');
    Route::get('/', [OrdemServicoController::class, 'index'])->name('index');
    Route::get('create', [OrdemServicoController::class, 'create'])->name('create');
    Route::post('/', [OrdemServicoController::class, 'store'])->name('store');
    Route::get('{ordem_servico}/pdf', [OrdemServicoController::class, 'pdf'])->name('pdf');
    Route::get('{ordem_servico}', [OrdemServicoController::class, 'show'])->name('show');
    Route::post('{ordem_servico}/status', [OrdemServicoController::class, 'atualizarStatus'])->name('status');
    Route::post('{ordem_servico}/concluir', [OrdemServicoController::class, 'concluir'])->name('concluir');
});

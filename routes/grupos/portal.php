<?php

use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/portal', [PortalController::class, 'index'])->name('portal.index');
    Route::get('/portal/aluno/{aluno}', [PortalController::class, 'aluno'])->name('portal.aluno');
    Route::get('/portal/financeiro', [PortalController::class, 'financeiro'])->name('portal.financeiro');
    Route::get('/portal/avisos', [PortalController::class, 'avisos'])->name('portal.avisos');
    Route::get('/portal/transporte', [PortalController::class, 'transporte'])->name('portal.transporte');
    Route::get('/portal/cardapio', [PortalController::class, 'cardapio'])->name('portal.cardapio');
});

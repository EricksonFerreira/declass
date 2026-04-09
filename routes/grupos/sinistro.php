<?php

use App\Http\Controllers\SinistroController;
use Illuminate\Support\Facades\Route;

Route::prefix('sinistro')->name('sinistro.')->group(function () {
    Route::get('export', [SinistroController::class, 'export'])->name('export');
    Route::get('buscar-reserva', [SinistroController::class, 'buscarReservaPorVeiculoData'])->name('buscarReservaPorVeiculoData');
    Route::post('{id}/pagar-empresa', [SinistroController::class, 'confirmarPagamentoEmpresa'])->name('pagarEmpresa');
    Route::post('{id}/pagar-cliente', [SinistroController::class, 'confirmarPagamentoCliente'])->name('pagarCliente');
});

Route::resource('sinistro', SinistroController::class);

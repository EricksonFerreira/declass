<?php

use App\Http\Controllers\MultaController;
use Illuminate\Support\Facades\Route;

Route::prefix('multa')->name('multa.')->group(function () {
    Route::get('export', [MultaController::class, 'export'])->name('export');
    Route::get('buscar-reserva', [MultaController::class, 'buscarReservaPorVeiculoData'])->name('buscarReservaPorVeiculoData');
    Route::post('{id}/atribuir-ao-cliente', [MultaController::class, 'atribuirAoCliente'])->name('atribuirAoCliente');
    Route::post('{id}/confirmar-pagamento', [MultaController::class, 'confirmarPagamento'])->name('confirmarPagamento');
});

Route::resource('multa', MultaController::class);
